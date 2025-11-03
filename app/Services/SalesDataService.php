<?php

namespace App\Services;

use App\Models\SalesReport;
use App\Models\Orders;
use App\Models\SkuMaster;
use App\Models\ReturnReport;
use App\Models\ExtraReturnReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Illuminate\Support\Facades\Storage;

class SalesDataService
{
    /**
     * Public: Get paginated sales list with computed metrics
     */
    public function getSalesList(int $perPage = 100): array
    {
        $salesData = $this->getBaseSalesQuery()->paginate($perPage);
        $merged = $this->prepareSalesData(collect($salesData->items()));

        return [
            'current_page' => $salesData->currentPage(),
            'per_page'     => $salesData->perPage(),
            'total'        => $salesData->total(),
            'last_page'    => $salesData->lastPage(),
            'data'         => $merged->values(),
        ];
    }

    /**
     * Public: Get totals across all data (non-paginated)
     */
    public function getSalesTotals(): array
    {
        $allSalesData = $this->getBaseSalesQuery()->get();
        $merged = $this->prepareSalesData($allSalesData);
        return $this->calculateTotals($merged);
    }

    /**
     * Private: Step 1 – Base query
     */
    private function getBaseSalesQuery()
    {
        return SalesReport::query()
            ->select([
                DB::raw('MAX(order_item_id) as order_item_id'),
                DB::raw('MAX(product_title_desc) as product_title_desc'),
                DB::raw('TRIM(BOTH \'\"\' FROM MAX(fsn)) as fsn'),
                DB::raw('TRIM(REPLACE(MAX(sku), "SKU:", "")) as sku'),
                DB::raw('MAX(igst_rate) as igst_rate'),
                DB::raw('MAX(cgst_rate) as cgst_rate'),
                DB::raw('MAX(sgst_rate) as sgst_rate'),
                DB::raw('MAX(item_quantity) as item_quantity'),
                DB::raw('MAX(buyer_delivery_state) as buyer_delivery_state'),
            ])
            ->groupBy('order_item_id');
    }

    /**
     * Private: Step 2–6 – Compute metrics for each sale record
     */
    private function prepareSalesData(Collection $salesData): Collection
    {
        $orderItemIds = $salesData->pluck('order_item_id')->filter()->unique()->toArray();
        $productIds   = $salesData->pluck('fsn')->filter()->unique()->toArray();

        // Orders totals
        $orders = Orders::query()
            ->select([
                'order_item_id',
                DB::raw('SUM(sale_amount) as total_sales_amount'),
                DB::raw('SUM(total_offer_amount) as total_offer_amount'),
                DB::raw('SUM(refund) as total_refund'),
                DB::raw('SUM(my_share) as total_my_share'),
                DB::raw('SUM(customer_addons_amount) as total_customer_addons'),
                DB::raw('SUM(marketplace_fee) as total_marketplace_fee'),
                DB::raw('SUM(taxes) as total_taxes'),
                DB::raw('SUM(protection_fund) as total_protection_fund'),
                DB::raw('SUM(bank_settlement_value) as total_bank_settlement'),
            ])
            ->whereIn('order_item_id', $orderItemIds)
            ->groupBy('order_item_id')
            ->get()
            ->keyBy('order_item_id');

        // SKU master
        $skuMasters = SkuMaster::query()
            ->whereIn('product_id', $productIds)
            ->select(['product_id', 'cost_price_with_gst', 'gst'])
            ->get()
            ->keyBy('product_id');

        // Return data
        $returnReports = ReturnReport::query()
            ->whereIn(DB::raw("REPLACE(order_item_id, 'OI:', '')"), $orderItemIds)
            ->select(['order_item_id', 'return_type'])
            ->get()
            ->keyBy('order_item_id');

        $extraReturnReports = ExtraReturnReport::query()
            ->whereIn('order_item_id', $orderItemIds)
            ->select(['order_item_id', 'return_type'])
            ->get()
            ->keyBy('order_item_id');

        // Compute derived fields
        return $salesData->map(function ($sale) use ($orders, $skuMasters, $returnReports, $extraReturnReports) {
            $orderItemId = $sale->order_item_id ?? null;
            $fsn = $sale->fsn ?? null;

            $order = $orders->get($orderItemId);
            $sku = $skuMasters->get($fsn);

            $productTitle = trim(preg_replace('/["\\\\]+/', '', $sale->product_title_desc));
            $skuCode      = trim(preg_replace('/["\\\\]+/', '', str_replace('SKU:', '', $sale->sku)));

            $total_sales_amount    = (float)($order->total_sales_amount ?? 0);
            $total_offer_amount    = (float)($order->total_offer_amount ?? 0);
            $total_refund          = (float)($order->total_refund ?? 0);
            $total_my_share        = (float)($order->total_my_share ?? 0);
            $total_customer_addons        = (float)($order->total_customer_addons ?? 0);
            $total_marketplace_fee = (float)($order->total_marketplace_fee ?? 0);
            $total_taxes           = (float)($order->total_taxes ?? 0);
            $total_protection_fund = (float)($order->total_protection_fund ?? 0);
            $total_bank_settlement = (float)($order->total_bank_settlement ?? 0);

            $cost_price_with_gst = (float)($sku->cost_price_with_gst ?? 0);
            $gst_rate_product    = (float)($sku->gst ?? 0);
            $item_quantity       = (float)($sale->item_quantity ?? 0);
            $qty = (float)($sale->item_quantity ?? 0);
            $igst_rate           = (float)($sale->igst_rate ?? 0);
            $cgst_rate           = (float)($sale->cgst_rate ?? 0);
            $sgst_rate           = (float)($sale->sgst_rate ?? 0);

            $returned_orders = 0;
            $unknown_orders = 0;
            $courier_return = 0;
            $customer_return = 0;
            $settlementDiff = $total_bank_settlement - $total_protection_fund;
            if ($settlementDiff > 0) {
                $order_status = 'Delivered';
            } else {
                $return = $returnReports->get($orderItemId);
                $extra  = $extraReturnReports->get($orderItemId);
                $order_status = $return->return_type ?? $extra->return_type ?? 'Unknown';
                $cost_price_with_gst = (float)0;
                if($order_status == 'Unknown'){
                    $unknown_orders = $item_quantity;
                }else{
                    $returned_orders = $item_quantity;
                    $courier_return = $order_status == 'courier_return' ? $item_quantity : 0;
                    $customer_return = $order_status == 'customer_return' ? $item_quantity : 0;
                }
                $item_quantity = (float)0;
            }

            $principal = $total_sales_amount + $total_offer_amount + $total_refund + $total_my_share + $total_customer_addons;
            $tax_rate = $igst_rate > 0 ? $igst_rate : $cgst_rate + $sgst_rate;

            $basic_on_principal = $principal / (1 + ($tax_rate / 100));
            $product_tax = $principal - $basic_on_principal;

            $cost_price_with_qty   = $cost_price_with_gst * $item_quantity;
            $cost_price_without_gst = $gst_rate_product > 0
                ? $cost_price_with_qty / (1 + ($gst_rate_product / 100))
                : $cost_price_with_qty;

            $gst_amount      = $cost_price_with_qty - $cost_price_without_gst;
            $basic_on_sales  = $basic_on_principal + $total_marketplace_fee + $total_protection_fund;
            $gst_on_sales    = $product_tax + $total_taxes;
            $diff_on_basics  = $basic_on_sales - $cost_price_without_gst;
            $diff_of_gst     = $gst_on_sales - $gst_amount;
            $gross_profit    = $diff_on_basics - $diff_of_gst;

            return [
                'order_item_id'         => $orderItemId,
                'qty'=>$qty,
                'product_title_desc'    => $productTitle,
                'fsn'                   => $fsn,
                'sku'                   => $skuCode,
                'igst_rate'             => $igst_rate,
                'cgst_rate'             => $cgst_rate,
                'sgst_rate'             => $sgst_rate,
                'item_quantity'         => $item_quantity,
                'buyer_delivery_state'  => $sale->buyer_delivery_state,
                'total_sales_amount'    => $total_sales_amount,
                'total_offer_amount'    => $total_offer_amount,
                'total_refund'          => $total_refund,
                'total_my_share'        => $total_my_share,
                'total_customer_addons'        => $total_customer_addons,
                'total_marketplace_fee' => $total_marketplace_fee,
                'total_taxes'           => $total_taxes,
                'total_protection_fund' => $total_protection_fund,
                'total_bank_settlement' => $total_bank_settlement,
                'cost_price_with_gst'   => $cost_price_with_gst,
                'gst'                   => $gst_rate_product,
                'order_status'          => $order_status,
                'principal'             => round($principal, 2),
                'tax_rate'              => $tax_rate,
                'basic_on_principal'    => round($basic_on_principal, 2),
                'product_tax'           => round($product_tax, 2),
                'cost_price_with_qty'   => round($cost_price_with_qty, 2),
                'cost_price_without_gst'=> round($cost_price_without_gst, 2),
                'gst_amount'            => round($gst_amount, 2),
                'basic_on_sales'        => round($basic_on_sales, 2),
                'gst_on_sales'          => round($gst_on_sales, 2),
                'diff_on_basics'        => round($diff_on_basics, 2),
                'diff_of_gst'           => round($diff_of_gst, 2),
                'gross_profit'          => round($gross_profit, 2),
                'returned_orders'       => $returned_orders,
                'unknown_orders'        => $unknown_orders,
                'courier_return'        => $courier_return,
                'customer_return'       => $customer_return,
            ];
        });
    }

    /**
     * Private: Step 7 – Calculate totals
     */
    private function calculateTotals(Collection $merged): array
    {
        return [
            'item_quantity'    => $merged->sum('item_quantity'),
            'total_qty'    => $merged->sum('qty'),
            'total_sales_amount'    => $merged->sum('total_sales_amount'),
            'total_offer_amount'    => $merged->sum('total_offer_amount'),
            'total_refund'          => $merged->sum('total_refund'),
            'total_my_share'        => $merged->sum('total_my_share'),
            'total_customer_addons'        => $merged->sum('total_customer_addons'),
            'total_marketplace_fee' => $merged->sum('total_marketplace_fee'),
            'total_taxes'           => $merged->sum('total_taxes'),
            'total_protection_fund' => $merged->sum('total_protection_fund'),
            'total_bank_settlement' => $merged->sum('total_bank_settlement'),
            'principal'             => $merged->sum('principal'),
            'product_tax'           => $merged->sum('product_tax'),
            'basic_on_principal'    => $merged->sum('basic_on_principal'),
            'cost_price_with_qty'   => $merged->sum('cost_price_with_qty'),
            'cost_price_without_gst'=> $merged->sum('cost_price_without_gst'),
            'gst_amount'            => $merged->sum('gst_amount'),
            'basic_on_sales'        => $merged->sum('basic_on_sales'),
            'gst_on_sales'          => $merged->sum('gst_on_sales'),
            'diff_on_basics'        => $merged->sum('diff_on_basics'),
            'diff_of_gst'           => $merged->sum('diff_of_gst'),
            'gross_profit'          => $merged->sum('gross_profit'),
            'total_returned_orders' => $merged->sum('returned_orders'),
            'total_unknown_orders'  => $merged->sum('unknown_orders'),
            'total_courier_return'  => $merged->sum('courier_return'),
            'total_customer_return' => $merged->sum('customer_return'),
        ];
    }

    public function getOtherSettlementsTotals(array $filters = []): array
    {
        $ads = DB::table('ads');
        $google = DB::table('google_ads_services');
        $mpFee = DB::table('mp_fee_rebate');
        $nonOrderSpf = DB::table('non_order_spf');
        $storageRecall = DB::table('storage_recall');

        // Optional filters example (you can expand this anytime)
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            foreach ([$ads, $google, $mpFee, $nonOrderSpf, $storageRecall] as $query) {
                $query->whereBetween('settlement_date', [$filters['start_date'], $filters['end_date']]);
            }
        }

        if (!empty($filters['marketplace'])) {
            foreach ([$ads, $google, $mpFee, $nonOrderSpf, $storageRecall] as $query) {
                $query->where('marketplace', $filters['marketplace']);
            }
        }

        // Execute each aggregate query individually
        $adsTotal = (float) ($ads->sum('settlement_value') ?? 0);
        $googleTotal = (float) ($google->sum('settlement_value') ?? 0);
        $mpFeeTotal = (float) ($mpFee->sum('settlement_value') ?? 0);
        $nonOrderSpfTotal = (float) ($nonOrderSpf->sum('settlement_value') ?? 0);
        $storageRecallTotal = (float) ($storageRecall->sum('settlement_value') ?? 0);

        // Compute combined total
        $totalSettlementValue = round(
            $adsTotal + $googleTotal + $mpFeeTotal + $nonOrderSpfTotal + $storageRecallTotal,
            2
        );

        return [
            'ads_total'             => round($adsTotal, 2),
            'google_ads_total'      => round($googleTotal, 2),
            'mp_fee_rebate_total'   => round($mpFeeTotal, 2),
            'non_order_spf_total'   => round($nonOrderSpfTotal, 2),
            'storage_recall_total'  => round($storageRecallTotal, 2),
            'total_settlement_value'=> $totalSettlementValue,
        ];
    }

    public function exportSalesListToExcel(array $filters = []): string
    {
        // Step 1: Get all sales data (you can apply filters in future)
        $salesData = $this->getBaseSalesQuery();

        // Future filters (example placeholders)
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $salesData->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        $salesCollection = $this->prepareSalesData($salesData->get());

        // Step 2: Create export file path
        $fileName = 'sales_list_export_' . now()->format('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/exports/' . $fileName);

        // Ensure directory exists
        Storage::makeDirectory('exports');

        // Step 3: Initialize Box/Spout writer
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePath);

        // Step 4: Define and write headers
        $headers = [
            'Order Item ID', 'Product Title', 'FSN', 'SKU', 'Item Quantity',
            'Buyer State', 'Total Sales', 'Offer', 'Refund', 'My Share',
            'Customer Addons', 'Marketplace Fee', 'Taxes', 'Protection Fund',
            'Bank Settlement', 'Cost Price (With GST)', 'Cost Price (Without GST)',
            'Gross Profit', 'Order Status', 'qty', 'basic_on_principal', 'product_tax',
            'cost_price_with_qty', 'gst_amount', 'basic_on_sales', 'gst_on_sales',
            'diff_on_basics', 'diff_of_gst'
        ];

        $writer->addRow(WriterEntityFactory::createRowFromArray($headers));

        // Step 5: Write rows
        foreach ($salesCollection as $sale) {
            $row = WriterEntityFactory::createRowFromArray([
                $sale['order_item_id'],
                $sale['product_title_desc'],
                $sale['fsn'],
                $sale['sku'],
                $sale['item_quantity'],
                $sale['buyer_delivery_state'],
                $sale['total_sales_amount'],
                $sale['total_offer_amount'],
                $sale['total_refund'],
                $sale['total_my_share'],
                $sale['total_customer_addons'],
                $sale['total_marketplace_fee'],
                $sale['total_taxes'],
                $sale['total_protection_fund'],
                $sale['total_bank_settlement'],
                $sale['cost_price_with_gst'],
                $sale['cost_price_without_gst'],
                $sale['gross_profit'],
                $sale['order_status'],
                $sale['qty'],
                $sale['basic_on_principal'],
                $sale['product_tax'],
                $sale['cost_price_with_qty'],
                $sale['gst_amount'],
                $sale['basic_on_sales'],
                $sale['gst_on_sales'],
                $sale['diff_on_basics'],
                $sale['diff_of_gst'],
            ]);
            $writer->addRow($row);
        }

        // Step 6: Finalize and return path
        $writer->close();

        return $filePath;
    }

    public function getTopFSNPerformance(array $options = []): array
    {
        /**
         * Available $options:
         * - 'metric'  => 'profit' | 'loss' | 'sales' | 'quantity' | 'all' (default: 'profit')
         * - 'limit'   => int|null (default: 5)
         * - 'fsn'     => string|null (to fetch single FSN stats)
         */

        $metric = $options['metric'] ?? 'profit';
        $limit  = $options['limit'] ?? 5;
        $fsnFilter = $options['fsn'] ?? null;
        $withSummary = $options['with_summary'] ?? false;
        $summaryOnly = $options['summary_only'] ?? false;

        // Step 1: Get processed sales data
        $salesData = $this->prepareSalesData($this->getBaseSalesQuery()->get());

        // Step 2: Group by FSN and aggregate core metrics
        $fsnSummary = $salesData
            ->groupBy('fsn')
            ->map(function ($items) {
                return [
                    'fsn'                => $items->first()['fsn'],
                    'product_title_desc' => $items->first()['product_title_desc'],
                    'total_gross_profit' => $items->sum('gross_profit'),
                    'total_sales'        => $items->sum('total_sales_amount'),
                    'total_customer_return'          => $items->sum('customer_return'),
                    'total_item_qty'          => $items->sum('item_quantity'),
                    'average_profit'     => $items->avg('gross_profit'),
                    'average_sales'      => $items->avg('total_sales_amount'),
                ];
            })
            ->values();

        if ($summaryOnly) {
            return [
                'total_fsns'        => $fsnSummary->count(),
                'total_sales_value' => round($fsnSummary->sum('total_sales'), 2),
                'total_qty_sold'    => $fsnSummary->sum('total_qty'),
                'total_item_qty_sold'    => $fsnSummary->sum('total_item_qty'),
                'avg_profit'        => round($fsnSummary->avg('total_gross_profit'), 2),
                'max_profit'        => round($fsnSummary->max('total_gross_profit'), 2),
                'min_profit'        => round($fsnSummary->min('total_gross_profit'), 2),
            ];
        }

        // Optional: Single FSN detail fetch
        if ($fsnFilter) {
            $single = $fsnSummary->firstWhere('fsn', $fsnFilter);
            return [
                'fsn'     => $single['fsn'] ?? $fsnFilter,
                'details' => $single ?? null,
                'message' => $single ? 'FSN details retrieved successfully.' : 'FSN not found.',
            ];
        }

        // Step 3: Decide sorting logic based on metric
        $sorted = match ($metric) {
            'profit'   => $fsnSummary->sortByDesc('total_gross_profit'),
            'loss'     => $fsnSummary->sortBy('total_gross_profit'),
            'sales'    => $fsnSummary->sortByDesc('total_sales'),
            'quantity' => $fsnSummary->sortByDesc('total_item_qty'),
            'customer_return' => $fsnSummary->sortByDesc('total_customer_return'),
            default    => $fsnSummary->sortByDesc('total_gross_profit'),
        };

        // Step 4: Apply limit only if not fetching all
        if ($metric !== 'all' && $limit !== null) {
            $sorted = $sorted->take($limit);
        }

        // Step 5: Build summary stats
        $summary = [
            'total_fsns'        => $fsnSummary->count(),
            'total_sales_value' => round($fsnSummary->sum('total_sales'), 2),
            'total_qty_sold'    => $fsnSummary->sum('total_qty'),
            'avg_profit'        => round($fsnSummary->avg('total_gross_profit'), 2),
            'max_profit'        => round($fsnSummary->max('total_gross_profit'), 2),
            'min_profit'        => round($fsnSummary->min('total_gross_profit'), 2),
        ];

        $response = [
            'data'     => $sorted->values(),
        ];

        if ($withSummary) {
            $response['criteria'] = ucfirst($metric);
            $response['limit'] = $limit;
            $response['summary'] = $summary;
        }

        // Step 6: Final structured response
        return $response;
    }

    public function getTopSalesStates(int $limit = 5, string $sort = 'desc', bool $withSummary = false): array
    {
        // Step 1: Get processed sales data
        $salesData = $this->prepareSalesData($this->getBaseSalesQuery()->get());

        // Step 2: Group by buyer_delivery_state and sum principal (total sales)
        $stateSummary = $salesData
            ->groupBy('buyer_delivery_state')
            ->map(function ($items, $state) {
                return [
                    'state'             => $state ?? 'Unknown',
                    'total_principal'   => $items->sum('principal'),
                    'total_sales'       => $items->sum('total_sales_amount'),
                    'total_qty'         => $items->sum('qty'),
                    'total_gross_profit'=> $items->sum('gross_profit'),
                    'total_orders'      => $items->count(),
                ];
            })
            ->values();

        // Step 3: Sort by total principal
        $sorted = $sort === 'asc'
            ? $stateSummary->sortBy('total_principal')
            : $stateSummary->sortByDesc('total_principal');

        // Step 4: Take top N states
        $topStates = $sorted->take($limit)->values();

        // Step 5: Optional summary
        $response = [
            'top_sales_states' => $topStates,
        ];

        if ($withSummary) {
            $response['summary'] = [
                'total_states'        => $stateSummary->count(),
                'grand_total_sales'   => round($stateSummary->sum('total_sales'), 2),
                'grand_total_principal' => round($stateSummary->sum('total_principal'), 2),
                'grand_total_qty'     => $stateSummary->sum('total_qty'),
                'avg_sales_per_state' => round($stateSummary->avg('total_sales'), 2),
                'max_sales_state'     => $stateSummary->sortByDesc('total_sales')->first()['state'] ?? null,
                'max_sales_value'     => round($stateSummary->max('total_sales'), 2),
            ];
        }

        return $response;
    }



}
