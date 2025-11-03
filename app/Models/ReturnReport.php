<?php

namespace App\Models;

use App\Traits\HasSummableColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnReport extends Model
{
    use HasFactory, HasSummableColumns;
}
