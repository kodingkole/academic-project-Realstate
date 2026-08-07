<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminModuleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvestorAuthController;
use App\Http\Controllers\InvestorDashboardController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicController::class, 'landing'])
    ->name('landing');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
|
| Logged-in user login page খুলতে পারবে না।
|
*/

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.attempt');

Route::get('/investor/login', [InvestorAuthController::class, 'showLogin'])
    ->name('investor.login');
Route::post('/investor/login', [InvestorAuthController::class, 'login'])
    ->name('investor.login.attempt');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
|
| শুধু logged-in user এই routes access করতে পারবে।
|
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/portal/admin', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::prefix('/portal/admin/{module}')
        ->whereIn('module', array_keys(AdminModuleController::MODULES))
        ->group(function () {
            Route::get('/', [AdminModuleController::class, 'index'])->name('admin.modules.index');
            Route::get('/create', [AdminModuleController::class, 'create'])->name('admin.modules.create');
            Route::post('/', [AdminModuleController::class, 'store'])->name('admin.modules.store');
            Route::get('/{record}/edit', [AdminModuleController::class, 'edit'])->name('admin.modules.edit');
            Route::put('/{record}', [AdminModuleController::class, 'update'])->name('admin.modules.update');
            Route::delete('/{record}', [AdminModuleController::class, 'destroy'])->name('admin.modules.destroy');
        });

});

Route::middleware(['auth', 'role:investor'])->group(function () {
    Route::get('/portal/investor', [InvestorDashboardController::class, 'index'])
        ->name('investor.dashboard');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
