<?php

use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| All API routes are prefixed with /api by default.
| We keep this file minimal for now; more routes will be added step-by-step.
*/

Route::get('/health', HealthController::class);
