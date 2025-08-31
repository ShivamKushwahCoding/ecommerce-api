<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportError extends Model
{
    protected $fillable = [
        'import_job_id',
        'row_data',
        'error_message',
        'resolved',
    ];

    protected $casts = [
        'row_data' => 'array',
        'resolved' => 'boolean',
    ];
}
