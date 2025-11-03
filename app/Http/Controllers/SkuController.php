<?php

namespace App\Http\Controllers;

use App\Models\SalesReport;
use App\Models\SkuMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkuController extends Controller
{
    public function index(){
        $skus = SkuMaster::all();
        return response()->json(['message' => 'SKU Mappings', 'skus' => $skus], 200);
    }

    public function requiredSkus(Request $request){
        $missingProducts = DB::table('sales_report as s')
            ->leftJoin('sku_masters as sm', 'sm.product_id', '=', DB::raw('REPLACE(s.fsn, "\"", "")'))
            ->whereNull('sm.product_id')
            ->distinct()
            ->get([
                DB::raw("REPLACE(s.fsn, '\"', '') as product_id"),
                DB::raw("REPLACE(s.product_title_desc, '\"', '') as product_name"),
                DB::raw("REPLACE(s.sku, '\"', '') as sku"),
            ]);
        return response()->json(['message' => 'Required SKUs', 'skus' => $missingProducts], 200);
    }
}
