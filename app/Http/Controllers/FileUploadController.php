<?php
namespace App\Http\Controllers;

use App\Models\UploadedFile;
use App\Services\ExcelReaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file         = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $storedName   = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path         = $file->storeAs('uploads', $storedName);

        $uploadedFile = UploadedFile::create([
            'original_name' => $originalName,
            'stored_name'   => $storedName,
            'path'          => $path,
            'size'          => $file->getSize(),
            'uploaded_by'   => Auth::id(),
        ]);

        return response()->json([
            'message'   => 'File uploaded successfully',
            'file_id'   => $uploadedFile->id,
            'file_name' => $uploadedFile->original_name,
        ], 201);
    }

    public function getHeaders($id, ExcelReaderService $excelReader)
    {
        set_time_limit(0);

        $uploadedFile = UploadedFile::findOrFail($id);

        $sheets = $excelReader->getSheetsAndHeaders($uploadedFile->path);

        return response()->json([
            'file_id'   => $uploadedFile->id,
            'file_name' => $uploadedFile->original_name,
            'sheets'    => $sheets,
        ]);
    }
}