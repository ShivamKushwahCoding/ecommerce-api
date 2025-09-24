<?php

namespace App\Observers;

use App\Models\SalesReport;

class SalesReportObserver
{
    /**
     * Handle the SalesReport "created" event.
     */
    public function created(SalesReport $salesReport): void
    {
        //
    }

    /**
     * Handle the SalesReport "updated" event.
     */
    public function updated(SalesReport $salesReport): void
    {
        //
    }

    /**
     * Handle the SalesReport "deleted" event.
     */
    public function deleted(SalesReport $salesReport): void
    {
        //
    }

    /**
     * Handle the SalesReport "restored" event.
     */
    public function restored(SalesReport $salesReport): void
    {
        //
    }

    /**
     * Handle the SalesReport "force deleted" event.
     */
    public function forceDeleted(SalesReport $salesReport): void
    {
        //
    }
}
