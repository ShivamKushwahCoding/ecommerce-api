<?php
namespace App\Services;

use App\Models\ExcelMapping;
use App\Models\ExtraReturnReport;
use App\Models\Permission;
use App\Models\ReturnReport;
use App\Models\Role;
use App\Models\SalesReport;
use App\Models\UploadedFile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
                // 'returns_summary'              => $this->getReturns(),
            ];
        });
    }

    public function getClientDashboardData()
    {
        $returns = $this->getReturns();
        return Cache::remember('client_dashboard', 60, function () use ($returns) {
            return [
                'sales_total'                   => SalesReport::sumOfColumn('final_invoice_amount', 'event_sub_type', 'Sale'),
                'cancellations_total'           => SalesReport::sumOfColumn('final_invoice_amount', 'event_sub_type', 'Cancellation'),
                'sales_total_count'             => SalesReport::sumOfColumn('item_quantity', 'event_sub_type', 'Sale'),
                'cancellations_count'           => SalesReport::sumOfColumn('item_quantity', 'event_sub_type', 'Cancellation'),
                'sales_total_order_count'       => SalesReport::countDistinctOrderWhereEventSubTypeIn('order_id', 'event_sub_type', 'Sale'),
                'cancellations_order_count'     => SalesReport::countDistinctOrderWhereEventSubTypeIn('order_id', 'event_sub_type', 'Cancellation'),
               'courier_return_total_count' => $returns[0]['courier_return_total_count'] ?? null,
               'courier_return_total' => $returns[0]['courier_return_total'] ?? null,
               'courier_return_total_qty' => $returns[0]['courier_return_total_qty'] ?? null,
               'customer_return_total_count' => $returns[1]['customer_return_total_count'] ?? null,
               'customer_return_total' => $returns[1]['customer_return_total'] ?? null,
               'customer_return_total_qty' => $returns[1]['customer_return_total_qty'] ?? null,
                'unknown_return_total_count' => $returns[2]['Unknown_total_count'] ?? null,
                'unknown_return_total' => $returns[2]['Unknown_total'] ?? null,
                'unknown_return_total_qty' => $returns[2]['Unknown_total_qty'] ?? null,
            ];
        });
    }

    private function getReturns()
    {
        $arr1 = SalesReport::where('event_sub_type', 'return')->pluck('order_item_id');
        $arr2 = SalesReport::where('event_sub_type', 'Return Cancellation')->pluck('order_item_id');

        // Find IDs present in arr1 but not in arr2
        $orderIds = array_diff($arr1->toArray(), $arr2->toArray());

        // Step 1: Fetch Sales
        $sales = SalesReport::whereIn(DB::raw("SUBSTRING_INDEX(order_item_id, ':', -1)"), $orderIds)
            ->get(['order_item_id', 'item_quantity', 'final_invoice_amount']);

        // Step 2: Fetch Returns
        $returns = ReturnReport::whereIn(DB::raw("SUBSTRING_INDEX(order_item_id, ':', -1)"), $orderIds)
            ->get(['order_item_id', 'return_type']);

        // Step 3: Merge
        $merged = $sales->map(function ($sale) use ($returns) {
            $numericId = explode(':', $sale->order_item_id)[1] ?? $sale->order_item_id;
            $return = $returns->first(fn($r) => str_ends_with($r->order_item_id, $numericId));

            return [
                'order_item_id' => $sale->order_item_id,
                'final_invoice_amount' => $sale->final_invoice_amount,
                'item_quantity' => $sale->item_quantity,
                'return_type' => isset($return->return_type) && $return->return_type ? $return->return_type : (ExtraReturnReport::where('order_item_id',$sale->order_item_id)->value('return_type') ? ExtraReturnReport::where('order_item_id',$sale->order_item_id)->value('return_type') : 'Unknown'),
            ];
        });

        // Step 4: Group by return_type and summarize
        $grouped = $merged->groupBy('return_type')->map(function ($items, $type) {
            return [
                // 'return_type' => $type,
                $type.'_total_count' => $items->count(),
                $type.'_total' => $items->sum('final_invoice_amount'),
                $type.'_total_qty' => $items->sum('item_quantity'),
            ];
        })->values();

        // Step 5: Return JSON response
        return $grouped;
    }
}
