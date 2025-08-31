<?php
namespace App\Http\Controllers;

use App\Jobs\ProcessExcelImport;
use App\Models\ExcelMapping;
use App\Models\ImportJob;
use App\Models\UploadedFile;

class ImportController extends Controller
{
    public function import($file_id)
    {
        $file = UploadedFile::findOrFail($file_id);

        // Ensure we actually have mappings for this file
        $hasMappings = ExcelMapping::where('file_id', $file->id)->exists();
        if (! $hasMappings) {
            return response()->json([
                'message' => 'No mappings found for this file. Please create mappings before importing.',
            ], 422);
        }

        $filePath = $file->path; // stored relative path (e.g. "uploads/uuid.xlsx")

        // Create job record
        $job = ImportJob::create([
            'file_id'        => $file->id,
            'status'         => 'pending',
            'total_rows'     => null,
            'processed_rows' => 0,
            'error_message'  => null,
        ]);

        // Dispatch job (streaming import; resolves mappings inside the job)
        ProcessExcelImport::dispatch($file->id, $filePath, $job->id);

        return response()->json([
            'message' => 'Import started',
            'job_id'  => $job->id,
        ]);
    }

    public function status($job_id)
    {
        $job = ImportJob::findOrFail($job_id);

        return response()->json([
            'status'         => $job->status,
            'total_rows'     => $job->total_rows,
            'processed_rows' => $job->processed_rows,
            'error_message'  => $job->error_message,
        ]);
    }
}
