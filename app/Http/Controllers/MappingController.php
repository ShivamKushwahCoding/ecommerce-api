<?php
namespace App\Http\Controllers;

use App\Models\ExcelMapping;
use Illuminate\Http\Request;

class MappingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file_id'         => 'required|exists:uploaded_files,id',
            'sheet_name'      => 'required|string',
            'table_name'      => 'required|string',
            'column_mappings' => 'required|array',
        ]);

        $mapping = ExcelMapping::create($validated);

        return response()->json([
            'message' => 'Mapping saved successfully',
            'mapping' => $mapping,
        ]);
    }

    public function update(Request $request, $id)
    {
        $mapping = ExcelMapping::findOrFail($id);

        $validated = $request->validate([
            'table_name'      => 'sometimes|string',
            'column_mappings' => 'sometimes|array',
        ]);

        $mapping->update($validated);

        return response()->json([
            'message' => 'Mapping updated successfully',
            'mapping' => $mapping,
        ]);
    }

    public function show($id)
    {
        return ExcelMapping::findOrFail($id);
    }

    public function destroy($id)
    {
        $mapping = ExcelMapping::findOrFail($id);
        $mapping->delete();

        return response()->json(['message' => 'Mapping deleted']);
    }
}