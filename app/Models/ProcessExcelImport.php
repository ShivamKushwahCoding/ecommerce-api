<?php
namespace App\Jobs;

use App\Models\ExcelMapping;
use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessExcelImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileId;
    protected $filePath;
    protected $jobId;

    public function __construct($fileId, $filePath, $jobId)
    {
        $this->fileId   = $fileId;
        $this->filePath = $filePath;
        $this->jobId    = $jobId;
    }

    public function handle(): void
    {
        $job         = ImportJob::find($this->jobId);
        $job->status = 'processing';
        $job->save();

        try {
            $spreadsheet = IOFactory::load($this->filePath);
            $mappings    = ExcelMapping::where('file_id', $this->fileId)->get();

            foreach ($mappings as $mapping) {
                $sheet = $spreadsheet->getSheetByName($mapping->sheet_name);
                if (! $sheet) {
                    continue;
                }

                $rows    = $sheet->toArray(null, true, true, true);
                $headers = array_shift($rows);

                $job->total_rows = count($rows);
                $job->save();

                $dbTable        = $mapping->table_name;
                $columnMappings = $mapping->column_mappings;

                $batch     = [];
                $batchSize = 500;
                $processed = 0;

                foreach ($rows as $row) {
                    $insertData = [];
                    foreach ($columnMappings as $excelCol => $dbCol) {
                        $excelIndex = array_search($excelCol, $headers);
                        if ($excelIndex !== false && isset($row[$excelIndex + 1])) {
                            $insertData[$dbCol] = $row[$excelIndex + 1];
                        }
                    }
                    if (! empty($insertData)) {
                        $batch[] = $insertData;
                    }

                    if (count($batch) >= $batchSize) {
                        DB::table($dbTable)->insert($batch);
                        $processed += count($batch);
                        $job->processed_rows = $processed;
                        $job->save();
                        $batch = [];
                    }
                }

                if (! empty($batch)) {
                    DB::table($dbTable)->insert($batch);
                    $processed += count($batch);
                    $job->processed_rows = $processed;
                    $job->save();
                }
            }

            $job->status = 'completed';
            $job->save();

        } catch (\Exception $e) {
            $job->status        = 'failed';
            $job->error_message = $e->getMessage();
            $job->save();
        }
    }
}