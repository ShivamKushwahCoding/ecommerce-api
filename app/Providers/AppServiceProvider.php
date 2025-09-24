<?php
namespace App\Providers;

use App\Models\ExcelMapping;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SalesReport;
use App\Models\UploadedFile;
use App\Models\User;
use App\Observers\ExcelMappingObserver;
use App\Observers\PermissionObserver;
use App\Observers\RoleObserver;
use App\Observers\SalesReportObserver;
use App\Observers\UploadedFileObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
        UploadedFile::observe(UploadedFileObserver::class);
        ExcelMapping::observe(ExcelMappingObserver::class);
        SalesReport::observe(SalesReportObserver::class);
    }
}
