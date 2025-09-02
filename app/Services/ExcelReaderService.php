<?php
namespace App\Services;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class ExcelReaderService
{
    public function getSheetsAndHeaders(string $filePath): array
    {
        $reader = ReaderEntityFactory::createReaderFromFile(storage_path("app/{$filePath}"));
        $reader->open(storage_path("app/{$filePath}"));

        $sheets = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            $sheetName = $sheet->getName();

            $headers = [];
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex === 1) {
                    $headers = $row->toArray();

                    // Trim headers and remove null/blank values
                    $headers = array_filter(array_map('trim', $headers));

                    break;
                }
            }

            // If no headers found, mark as empty
            if (empty($headers)) {
                $headers = ['(No headers found in first row)'];
            }

            $sheets[] = [
                'sheet_name' => $sheetName,
                'headers'    => $headers,
            ];
        }

        $reader->close();

        return $sheets;
    }
}