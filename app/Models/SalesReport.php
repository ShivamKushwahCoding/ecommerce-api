<?php
namespace App\Models;

use App\Traits\HasSummableColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReport extends Model
{
    use HasFactory, HasSummableColumns;

    protected $table = 'sales_report';

    public function countByGroupOfColumn($column, $conditionColumn = null, $conditionValue = null)
    {
        if ($conditionColumn && $conditionValue) {
            return $this->where($conditionColumn, $conditionValue)->count($column);
        }
        return $this->count($column);
    }
}
