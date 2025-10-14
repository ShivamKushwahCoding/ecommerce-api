<?php

use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\RolePermissionController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MappingController;
use Illuminate\Support\Facades\Route;

// Health
Route::get('/health', HealthController::class);

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    // Admin-only
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('permissions', PermissionController::class);
        Route::apiResource('users', UserController::class);

        // Role-permission management
        Route::get('roles/{role}/permissions', [RolePermissionController::class, 'index']);
        Route::post('roles/{role}/permissions', [RolePermissionController::class, 'store']);
        Route::delete('roles/{role}/permissions/{permission}', [RolePermissionController::class, 'destroy']);

        // User role & activation management
        Route::put('users/{user}/role', [UserController::class, 'assignRole']);
        Route::put('users/{user}/activate', [UserController::class, 'setActive']);

        //Upload file
        Route::post('/files/upload', [FileUploadController::class, 'upload']);
        Route::get('/files/{id}/headers', [FileUploadController::class, 'getHeaders']);

        //Mapping columns
        Route::prefix('mappings')->group(function () {
            Route::post('/', [MappingController::class, 'store']);
            Route::get('/list', [MappingController::class, 'index']);
            Route::put('/{id}', [MappingController::class, 'update']);
            Route::get('/{id}', [MappingController::class, 'show']);
            Route::delete('/{id}', [MappingController::class, 'destroy']);
        });

        //import data from excel to DB
        Route::post('/import/{file_id}', [ImportController::class, 'import']);
        Route::get('/import/status/{job_id}', [ImportController::class, 'status']);

    });
});