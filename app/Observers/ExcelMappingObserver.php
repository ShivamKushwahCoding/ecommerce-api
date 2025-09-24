<?php
namespace App\Observers;

use App\Models\ExcelMapping;
use Illuminate\Support\Facades\Cache;

class ExcelMappingObserver
{
    /**
     * Handle the ExcelMapping "created" event.
     */
    public function created(ExcelMapping $excelMapping): void
    {
        Cache::put('admin_dashboard');
    }

    /**
     * Handle the ExcelMapping "updated" event.
     */
    public function updated(ExcelMapping $excelMapping): void
    {
        Cache::put('admin_dashboard');
    }

    /**
     * Handle the ExcelMapping "deleted" event.
     */
    public function deleted(ExcelMapping $excelMapping): void
    {
        Cache::put('admin_dashboard');
    }

    /**
     * Handle the ExcelMapping "restored" event.
     */
    public function restored(ExcelMapping $excelMapping): void
    {
        //
    }

    /**
     * Handle the ExcelMapping "force deleted" event.
     */
    public function forceDeleted(ExcelMapping $excelMapping): void
    {
        //
    }
}
