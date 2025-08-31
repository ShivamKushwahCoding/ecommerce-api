<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'sheet_name',
        'table_name',
        'column_mappings',
    ];

    protected $casts = [
        'column_mappings' => 'array',
    ];
}