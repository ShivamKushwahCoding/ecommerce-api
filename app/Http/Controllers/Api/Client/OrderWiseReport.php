<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\ExtraReturnReport;
use App\Models\Orders;
use App\Models\ReturnReport;
use App\Models\SalesReport;
use App\Models\SkuMaster;
use App\Services\SalesDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderWiseReport extends Controller
{
    public function index(Request $request, SalesDataService $service)
    {        
        $data = $service->getSalesList($request->get('perPage', 100));
        return response()->json($data);
    }

    public function downloadReport(Request $request, SalesDataService $service)
    {        
        $filePath = $service->exportSalesListToExcel($request->all());

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

}
