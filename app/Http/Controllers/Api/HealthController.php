<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app'    => config('app.name'),
            'env'    => config('app.env'),
            'time'   => now()->toIso8601String(),
        ], 200);
    }
}
