<?php
// app/Traits/HasSummableColumns.php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSummableColumns
{
    /**
     * Sum a column with optional condition.
     *
     * @param string $column
     * @param string|null $conditionColumn
     * @param mixed|null $conditionValue
     * @return float|int
     */
    public static function sumOfColumn(string $column, ?string $conditionColumn = null, ?string $conditionValue = null): int
    {
        $query = static::query();

        if ($conditionColumn !== null && $conditionValue !== null) {
            $query->where($conditionColumn, $conditionValue);
        }

        return $query->sum($column);
    }

    public static function countDistinctOrderWhereEventSubTypeIn(string $column, ?string $conditionColumn = null, ?string $conditionValue = null): int
    {
        return static::query()
            ->where($conditionColumn, $conditionValue)
            ->count($column);
    }
}
