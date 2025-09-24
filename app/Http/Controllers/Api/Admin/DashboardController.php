<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminDashboardResource;
use App\Http\Resources\ClientDashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        if (Gate::allows('viewAdminDashboard')) {
            $data = $this->dashboardService->getAdminDashboardData();
            return new AdminDashboardResource((object) $data);
        }

        if (Gate::allows('viewClientDashboard')) {
            $data = $this->dashboardService->getClientDashboardData();
            return new ClientDashboardResource((object) $data);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }
}
