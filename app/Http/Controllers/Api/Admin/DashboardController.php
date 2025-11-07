<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminDashboardResource;
use App\Http\Resources\ClientDashboardResource;
use App\Models\Orders;
use App\Models\SalesReport;
use App\Models\SkuMaster;
use App\Services\DashboardService;
use App\Services\SalesDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request, SalesDataService $service)
    {
        if (Gate::allows('viewAdminDashboard')) {
            $data = $this->dashboardService->getAdminDashboardData();
            return new AdminDashboardResource((object) $data);
        }

        if (Gate::allows('viewClientDashboard')) {

            $otherSettlements = $service->getOtherSettlementsTotals();
            $totals = $service->getSalesTotals();

            $totals['total_expense'] = $totals['cost_price_with_qty'] - $totals['total_marketplace_fee'] - $otherSettlements['total_settlement_value'] - $totals['total_protection_fund'] - $totals['total_taxes'];
            // $totals['taxes'] = $totals['gst_amount'] + ($otherSettlements['total_settlement_value'] - ($otherSettlements['total_settlement_value'] / 1.18)) + $totals['total_taxes'];
            $totals['net_profit'] = $totals['principal'] - $totals['total_expense'];

            $response = [
                // Overview section
                'total_quantity'=> $totals['total_qty'],
                'net_qty'=>$totals['item_quantity'],
                'total_revenue'=>$totals['principal'],
                'total_expense'=>$totals['total_expense'],
                'net_profit'=>$totals['net_profit'],
                'gross_profit'=>$totals['gross_profit'],
                'total_taxes'=>$totals['total_taxes'],

                // Payment settlement overview
                'net_settlement_amount'=> $totals['total_bank_settlement'],
                'total_purchase_cost'=> $totals['cost_price_with_qty'],
                'ads_fees'=> $otherSettlements['ads_total'] + $otherSettlements['google_ads_total'],
                'other_expenses'=> $otherSettlements['mp_fee_rebate_total'] - $otherSettlements['non_order_spf_total'] - $otherSettlements['storage_recall_total'],
                'mp_fees'=> $totals['total_marketplace_fee'],
                'reimbursements'=> $totals['total_protection_fund'],

                // Order statistics
                'returned_orders'=> $totals['total_returned_orders'],
                'unknown_orders'=> $totals['total_unknown_orders'],
                'courier_return'=> $totals['total_courier_return'],
                'customer_return'=> $totals['total_customer_return'],

                // Sales performance
                'top_profit' => $service->getTopFSNPerformance(['metric' => 'profit']),
                'top_loss' => $service->getTopFSNPerformance(['metric' => 'loss']),
                'top_selling' => $service->getTopFSNPerformance(['metric' => 'quantity']),
                'top_customer_return' => $service->getTopFSNPerformance(['metric' => 'customer_return']),
                'fsn_summary' => $service->getTopFSNPerformance(['summary_only' => true]),
                'top_states' => $service->getTopSalesStates(5, 'desc'),

            ];

            return response()->json($response);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

}
