<?php
namespace App\Observers;

use App\Models\UploadedFile;

class UploadedFileObserver
{
    /**
     * Handle the UploadedFile "created" event.
     */
    public function created(UploadedFile $uploadedFile): void
    {
        // Cache::put('admin_dashboard');
    }

    /**
     * Handle the UploadedFile "updated" event.
     */
    public function updated(UploadedFile $uploadedFile): void
    {
        // Cache::put('admin_dashboard');
    }

    /**
     * Handle the UploadedFile "deleted" event.
     */
    public function deleted(UploadedFile $uploadedFile): void
    {
        // Cache::put('admin_dashboard');
    }

    /**
     * Handle the UploadedFile "restored" event.
     */
    public function restored(UploadedFile $uploadedFile): void
    {
        //
    }

    /**
     * Handle the UploadedFile "force deleted" event.
     */
    public function forceDeleted(UploadedFile $uploadedFile): void
    {
        //
    }
}
