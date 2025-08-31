<?php
namespace App\Jobs;

use App\Models\ExcelMapping;
use App\Models\ImportJob;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessExcelImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $fileId;
    protected string $relativeFilePath;
    protected int $importJobId;

    public $tries   = 1;
    public $timeout = 3600;

    public function __construct(int $fileId, string $relativeFilePath, int $importJobId)
    {
        $this->fileId           = $fileId;
        $this->relativeFilePath = $relativeFilePath;
        $this->importJobId      = $importJobId;
    }

    public function handle(): void
    {
        $job = ImportJob::find($this->importJobId);
        if (! $job) {
            return;
        }

        $job->status = 'processing';
        $job->save();

        $fullPath = storage_path('app/' . $this->relativeFilePath);

        try {
            $mappings = ExcelMapping::where('file_id', $this->fileId)->get();
            if ($mappings->isEmpty()) {
                throw new \RuntimeException('No mappings found for this file.');
            }

            // normalize mappings keys (lowercase+trim)
            $mappingsBySheet = [];
            foreach ($mappings as $map) {
                $normalized = [];
                foreach ($map->column_mappings as $excelHeader => $dbCol) {
                    $normalizedKey              = mb_strtolower(trim((string) $excelHeader));
                    $normalized[$normalizedKey] = $dbCol;
                }
                $mappingsBySheet[$map->sheet_name] = [
                    'table'   => $map->table_name,
                    'columns' => $normalized,
                ];
            }

            $reader = ReaderEntityFactory::createReaderFromFile($fullPath);
            $reader->open($fullPath);

            $batchSize      = 500;
            $grandProcessed = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                $sheetName = $sheet->getName();

                if (! isset($mappingsBySheet[$sheetName])) {
                    continue;
                }

                $tableName      = $mappingsBySheet[$sheetName]['table'];
                $columnMappings = $mappingsBySheet[$sheetName]['columns']; // normalized excel header => db column

                // detect numeric-like and datetime-like columns for this table
                $numericColumns = $this->getColumnsByType($tableName, [
                    'tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'numeric', 'float', 'double', 'real',
                ]);
                $datetimeColumns = $this->getColumnsByType($tableName, ['date', 'datetime', 'timestamp']);

                $headerIndex = []; // normalized header => index
                $batch       = [];

                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $cells = $row->toArray();

                    // header
                    if ($rowIndex === 1) {
                        foreach ($cells as $idx => $cellValue) {
                            $key = mb_strtolower(trim((string) $cellValue));
                            if ($key !== '') {
                                $headerIndex[$key] = $idx;
                            }
                        }
                        continue;
                    }

                    $mapped     = [];
                    $skipRow    = false;
                    $skipReason = null;

                    foreach ($columnMappings as $normExcelHeader => $dbCol) {
                        // if header not present in file, set null for that db column
                        if (! isset($headerIndex[$normExcelHeader])) {
                            $mapped[$dbCol] = null;
                            continue;
                        }

                        $pos = $headerIndex[$normExcelHeader];
                        $raw = $cells[$pos] ?? null;
                        if (is_string($raw)) {
                            $raw = trim($raw);
                        }

                        // datetime columns -> validate or convert
                        if (in_array($dbCol, $datetimeColumns, true)) {
                            // cleanDateValue returns:
                            // - false => invalid (skip row)
                            // - null => empty/NULL allowed (do not skip)
                            // - 'Y-m-d H:i:s' => valid
                            $cleanDate = $this->cleanDateValue($raw);
                            if ($cleanDate === false) {
                                $skipRow    = true;
                                $skipReason = "Invalid datetime for column '{$dbCol}': " . (($raw === null) ? 'NULL' : $raw);
                                break; // skip this row entirely
                            }
                            $mapped[$dbCol] = $cleanDate; // can be null or datetime string
                        }
                        // numeric columns -> clean (NA->null, commas removed, non-numeric->null)
                        elseif (in_array($dbCol, $numericColumns, true)) {
                            $mapped[$dbCol] = $this->cleanNumericValue($raw);
                        }
                        // other columns -> keep string/null
                        else {
                            $mapped[$dbCol] = ($raw === '' || $raw === null) ? null : $raw;
                        }
                    }

                    if ($skipRow) {
                        // Log skipped row into uploadInfo.log
                        $this->logSkippedRow($sheetName, $rowIndex, $cells, $skipReason);

                        // increment processed count (we treated this row as processed)
                        $grandProcessed++;
                        ImportJob::where('id', $this->importJobId)->update([
                            'processed_rows' => $grandProcessed,
                        ]);
                        continue;
                    }

                    // insert only rows that have some non-null data
                    if (count(array_filter($mapped, fn($v) => $v !== null && $v !== '')) > 0) {
                        $batch[] = $mapped;
                    }

                    if (count($batch) >= $batchSize) {
                        DB::table($tableName)->insert($batch);
                        $grandProcessed += count($batch);
                        $batch = [];

                        ImportJob::where('id', $this->importJobId)->update([
                            'processed_rows' => $grandProcessed,
                        ]);
                    }
                } // end rows for sheet

                // flush remaining
                if (! empty($batch)) {
                    DB::table($tableName)->insert($batch);
                    $grandProcessed += count($batch);
                    ImportJob::where('id', $this->importJobId)->update([
                        'processed_rows' => $grandProcessed,
                    ]);
                    $batch = [];
                }
            } // end sheets

            $reader->close();

            $job->status = 'completed';
            $job->save();
        } catch (\Throwable $e) {
            $job->status        = 'failed';
            $job->error_message = mb_substr($e->getMessage(), 0, 255);
            $job->save();

            Log::error("Import Job {$this->importJobId} failed: " . $e->getMessage(), [
                'file'  => $fullPath,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get columns by data types from INFORMATION_SCHEMA
     */
    protected function getColumnsByType(string $tableName, array $types): array
    {
        $rows = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
        ", [$tableName]);

        $cols = [];
        foreach ($rows as $r) {
            if (in_array(strtolower($r->DATA_TYPE), $types, true)) {
                $cols[] = $r->COLUMN_NAME;
            }
        }
        return $cols;
    }

    /**
     * Clean numeric values:
     * - treat 'NA','N/A','-','--', '', null as null
     * - remove commas & NBSP, strip currency symbols, then check is_numeric
     */
    protected function cleanNumericValue($raw)
    {
        if ($raw === null) {
            return null;
        }

        $str = trim((string) $raw);
        if ($str === '') {
            return null;
        }

        $upper = strtoupper($str);
        if (in_array($upper, ['NA', 'N/A', '-', '--', 'NULL'], true)) {
            return null;
        }

        $clean = str_replace([',', ' ', "\xc2\xa0"], '', $str);
        $clean = preg_replace('/[^\d\.\-eE]/', '', $clean);

        return is_numeric($clean) ? $clean : null;
    }

    /**
     * Clean/validate date values:
     * - return false => invalid -> caller will skip row & log it
     * - return null  => empty input accepted (do not skip)
     * - return string 'Y-m-d H:i:s' => valid date
     */
    protected function cleanDateValue($raw)
    {
        if ($raw === null) {
            return null; // treat empty as NULL
        }

        $str = trim((string) $raw);
        if ($str === '') {
            return null;
        }

        $upper = strtoupper($str);
        if (in_array($upper, ['NA', 'N/A', '-', '--', 'NULL'], true)) {
            // these are considered invalid for date fields in your requirement -> skip row
            return false;
        }

        // If the value is numeric, treat it as possible Excel serial date
        if (is_numeric($str)) {
            // basic Excel serial -> unix timestamp conversion (1900 system)
            $serial = (float) $str;
            // Excel serials are typically > 59 (skip leap-year bug), but handle generically
            $unix = ($serial - 25569) * 86400;
            if (! is_numeric($unix)) {
                return false;
            }

            // Some serials might be fractional (time); we keep time part
            $dt = @gmdate('Y-m-d H:i:s', (int) $unix);
            if ($dt === false) {
                return false;
            }

            return $dt;
        }

        // Try PHP parsing (strtotime)
        $ts = strtotime($str);
        if ($ts !== false) {
            return date('Y-m-d H:i:s', $ts);
        }

        // Try common formats: d/m/Y, d-m-Y, m/d/Y etc.
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y', 'Y/m/d', 'd M Y', 'd M, Y', 'Y-m-d H:i:s'];
        foreach ($formats as $fmt) {
            $d = \DateTime::createFromFormat($fmt, $str);
            if ($d !== false) {
                return $d->format('Y-m-d H:i:s');
            }
        }

        // not parseable -> skip row
        return false;
    }

    /**
     * Log skipped row details into storage/logs/uploadInfo.log
     */
    protected function logSkippedRow(string $sheetName, int $rowIndex, array $cells, string $reason): void
    {
        $logFile = storage_path('logs/uploadInfo.log');
        $entry   = [
            'timestamp' => now()->toDateTimeString(),
            'file_id'   => $this->fileId,
            'job_id'    => $this->importJobId,
            'sheet'     => $sheetName,
            'row'       => $rowIndex,
            'reason'    => $reason,
            'row_data'  => $cells,
        ];
        $line = json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        // ensure logs dir writable
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
