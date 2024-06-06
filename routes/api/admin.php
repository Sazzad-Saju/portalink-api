<?php
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:admin')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('get/login-user/permission', [AuthController::class, 'getAuthUserPermission']);
    
    //settings
    Route::get('/setting/get-logo', [SettingController::class, 'getLogo']);
});