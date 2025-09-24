<?php
namespace App\Services;

use App\Models\ExcelMapping;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SalesReport;
use App\Models\UploadedFile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getAdminDashboardData()
    {
        return Cache::remember('admin_dashboard', 60, function () {
            return [
                'total_users'    => User::count(),
                'active_users'   => User::where('is_active', true)->count(),
                'inactive_users' => User::where('is_active', false)->count(),
                'roles'          => Role::count(),
                'permissions'    => Permission::count(),
                'files_uploaded' => UploadedFile::count(),
                'mappings'       => ExcelMapping::count(),
            ];
        });
    }

    public function getClientDashboardData()
    {
        return Cache::remember('client_dashboard', 60, function () {
            return [
                'sales_total'                   => SalesReport::sumOfColumn('final_invoice_amount', 'event_sub_type', 'Sale'),
                'returns_total'                 => SalesReport::sumOfColumn('final_invoice_amount', 'event_sub_type', 'Return'),
                'returns_cancelled_total'       => SalesReport::sumOfColumn('final_invoice_amount', 'event_sub_type', 'Return Cancellation'),
                'cancellations_total'           => SalesReport::sumOfColumn('final_invoice_amount', 'event_sub_type', 'Cancellation'),
                'sales_total_count'             => SalesReport::sumOfColumn('item_quantity', 'event_sub_type', 'Sale'),
                'returns_total_count'           => SalesReport::sumOfColumn('item_quantity', 'event_sub_type', 'Return'),
                'returns_cancelled_count'       => SalesReport::sumOfColumn('item_quantity', 'event_sub_type', 'Return Cancellation'),
                'cancellations_count'           => SalesReport::sumOfColumn('item_quantity', 'event_sub_type', 'Cancellation'),
                'sales_total_order_count'       => SalesReport::countDistinctOrderWhereEventSubTypeIn('order_id', 'event_sub_type', ['Sale']),
                'returns_total_order_count'     => SalesReport::countDistinctOrderWhereEventSubTypeIn('order_id', 'event_sub_type', ['Return']),
                'returns_cancelled_order_count' => SalesReport::countDistinctOrderWhereEventSubTypeIn('order_id', 'event_sub_type', ['Return Cancellation']),
                'cancellations_order_count'     => SalesReport::countDistinctOrderWhereEventSubTypeIn('order_id', 'event_sub_type', ['Cancellation']),
            ];
        });
    }
}
