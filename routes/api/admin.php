<?php
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:admin')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('get/login-user/permission', [AuthController::class, 'getAuthUserPermission']);
    Route::get('get/customer-permission', [AuthController::class, 'getCustomerPermissions']);
    
    //settings
    Route::get('/setting/get-logo', [SettingController::class, 'getLogo']);
    Route::post('/update-logos', [SettingController::class, 'storeOrUpdateSiteLogs']);
    Route::post('/logo-delete', [SettingController::class, 'deleteLogo']);
    
    //customer
    Route::apiResource('customers', CustomerController::class);
});