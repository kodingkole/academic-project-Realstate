<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminModuleController;
use App\Http\Controllers\AdminInvestorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ErpController;
use App\Http\Controllers\InvestorAuthController;
use App\Http\Controllers\InvestorDashboardController;
use App\Http\Controllers\InvestorDocumentController;
use App\Http\Controllers\LandownerAuthController;
use App\Http\Controllers\NotificationApiController;
use App\Http\Controllers\PaymentApiController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicController::class, 'landing'])
    ->name('landing');
Route::get('/submit-land', [PublicController::class, 'submitLand'])->name('land.submit');
Route::post('/submit-land', [PublicController::class, 'submitLandPost'])->name('land.submit.store');

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
Route::get('/investor/register', [InvestorAuthController::class, 'showRegister'])
    ->name('investor.register');
Route::post('/investor/register', [InvestorAuthController::class, 'register'])
    ->name('investor.register.store');
Route::get('/investor/verify', [InvestorAuthController::class, 'showOtp'])->name('investor.otp.form');
Route::post('/investor/verify', [InvestorAuthController::class, 'verifyOtp'])->name('investor.otp.verify');

Route::get('/landowner/login', [LandownerAuthController::class, 'showLogin'])
    ->name('landowner.login');
Route::post('/landowner/login', [LandownerAuthController::class, 'login'])
    ->name('landowner.login.attempt');
Route::get('/landowner/register', [LandownerAuthController::class, 'showRegister'])
    ->name('landowner.register');
Route::post('/landowner/register', [LandownerAuthController::class, 'register'])
    ->name('landowner.register.store');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
|
| শুধু logged-in user এই routes access করতে পারবে।
|
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/portal/admin', [PortalController::class, 'adminDashboard'])
        ->name('admin.dashboard');

    Route::get('/portal/admin/erp', [PortalController::class, 'adminErpHub'])->name('admin.erp');
    Route::get('/portal/admin/projects', [PortalController::class, 'adminProjects'])->name('admin.projects');
    Route::get('/portal/admin/lawyers', [PortalController::class, 'adminLawyers'])->name('admin.lawyers');
    Route::post('/portal/admin/land-submissions/{submission}/assign-lawyer', [PortalController::class, 'assignLawyer'])->name('admin.submissions.assign');
    Route::post('/portal/admin/land-submissions/{submission}/approve', [PortalController::class, 'approveSubmission'])->name('admin.submissions.approve');
    Route::post('/portal/admin/land-submissions/{submission}/reject', [PortalController::class, 'rejectSubmission'])->name('admin.submissions.reject');
    Route::get('/portal/admin/audit/export', [PortalController::class, 'exportAuditLog'])->name('admin.audit.export');
    Route::get('/portal/admin/audit', [PortalController::class, 'adminAudit'])->name('admin.audit');
    Route::get('/portal/admin/investor-documents', [InvestorDocumentController::class, 'manage'])->name('admin.investor-documents');
    Route::post('/portal/admin/investor-documents', [InvestorDocumentController::class, 'store'])->name('admin.investor-documents.store');
    Route::get('/portal/admin/investors', [AdminInvestorController::class, 'index'])->name('admin.investors');
    Route::get('/portal/admin/investor-payments/{payment}/document/{type}', [AdminInvestorController::class, 'downloadDocument'])->name('admin.investor-payments.document');
    Route::patch('/portal/admin/investor-payments/{payment}/approve', [AdminInvestorController::class, 'approvePayment'])->name('admin.investor-payments.approve');
    Route::patch('/portal/admin/investor-payments/{payment}/reject', [AdminInvestorController::class, 'rejectPayment'])->name('admin.investor-payments.reject');

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

Route::prefix('erp')->middleware(['auth', 'portal.role:admin', 'erp.module:dashboard'])->group(function () {
    Route::get('/projects', fn (ErpController $controller, \App\Services\ErpIntelligenceService $ai) => $controller->index('projects', $ai))->name('erp.projects');
    Route::post('/projects', [ErpController::class, 'storeProject'])->name('erp.projects.store');
    Route::patch('/projects/{project}', [ErpController::class, 'updateProject'])->name('erp.projects.update');
    Route::post('/projects/{project}/milestones', [ErpController::class, 'storeMilestone'])->name('erp.milestones.store');
    Route::get('/tasks', fn (ErpController $controller, \App\Services\ErpIntelligenceService $ai) => $controller->index('tasks', $ai))->name('erp.tasks');
    Route::post('/tasks', [ErpController::class, 'storeTask'])->name('erp.tasks.store');
    Route::post('/tasks/{task}/progress', [ErpController::class, 'updateTaskProgress'])->name('erp.tasks.progress');
    Route::put('/tasks/{task}', [ErpController::class, 'updateTask'])->name('erp.tasks.update');
    Route::delete('/tasks/{task}', [ErpController::class, 'destroyTask'])->name('erp.tasks.destroy');
    Route::get('/inventory', fn (ErpController $controller, \App\Services\ErpIntelligenceService $ai) => $controller->index('inventory', $ai))->name('erp.inventory');
    Route::post('/inventory/materials', [ErpController::class, 'storeMaterial'])->name('erp.inventory.materials');
    Route::post('/inventory/transaction', [ErpController::class, 'stockTransaction'])->name('erp.inventory.transaction');
    Route::post('/inventory/auto-reorder', [ErpController::class, 'autoReorder'])->name('erp.inventory.reorder');
    Route::put('/inventory/materials/{material}', [ErpController::class, 'updateMaterial'])->name('erp.inventory.materials.update');
    Route::delete('/inventory/materials/{material}', [ErpController::class, 'destroyMaterial'])->name('erp.inventory.materials.destroy');
    Route::get('/finance', fn (ErpController $controller, \App\Services\ErpIntelligenceService $ai) => $controller->index('finance', $ai))->name('erp.finance');
    Route::post('/finance/expenses', [ErpController::class, 'storeExpense'])->name('erp.finance.expenses');
    foreach (['procurement','workforce','site-progress','inspections','documents','equipment'] as $module) {
        Route::get('/'.$module, fn (ErpController $controller, \App\Services\ErpIntelligenceService $ai) => $controller->index($module, $ai))->name('erp.'.$module);
        Route::post('/'.$module, fn (\Illuminate\Http\Request $request, ErpController $controller) => $controller->genericStore($request, $module))->name('erp.'.$module.'.store');
    }
    Route::post('/workforce/generate-payroll', [ErpController::class, 'generatePayroll'])->name('erp.workforce.payroll');
    Route::post('/procurement/suppliers', [ErpController::class, 'storeSupplier'])->name('erp.procurement.suppliers');
    Route::post('/procurement/purchase-requests', [ErpController::class, 'storePurchaseRequest'])->name('erp.procurement.requests');
    Route::post('/procurement/purchase-orders', [ErpController::class, 'createPurchaseOrder'])->name('erp.procurement.orders');
    Route::post('/workforce/qr-attendance', [ErpController::class, 'recordQrAttendance'])->name('erp.workforce.attendance');
    Route::post('/site-progress/reports', [ErpController::class, 'storeReport'])->name('erp.site-progress.reports');
    Route::post('/inspections/report', [ErpController::class, 'storeInspection'])->name('erp.inspections.report');
    Route::post('/documents/upload', [ErpController::class, 'storeDocument'])->name('erp.documents.upload');
    Route::post('/equipment/register', [ErpController::class, 'storeEquipment'])->name('erp.equipment.register');
});

Route::middleware(['auth', 'role:investor'])->group(function () {
    Route::get('/portal/investor', [InvestorDashboardController::class, 'index'])
        ->name('investor.dashboard');
    Route::get('/portal/investor/ledger', [InvestorDashboardController::class, 'ledger'])->name('investor.ledger');
    Route::get('/portal/investor/documents', [InvestorDashboardController::class, 'documents'])->name('investor.documents');
    Route::get('/portal/investor/notifications', [InvestorDashboardController::class, 'notifications'])->name('investor.notifications');
    Route::patch('/portal/investor/notifications/read-all', [InvestorDashboardController::class, 'markAllNotificationsRead'])->name('investor.notifications.read-all');
    Route::patch('/portal/investor/notifications/{notification}/read', [InvestorDashboardController::class, 'markNotificationRead'])->name('investor.notifications.read');
    Route::get('/portal/investor/documents/{document}/download', [InvestorDocumentController::class, 'download'])->name('investor.documents.download');
    Route::post('/portal/investor/bookings/reserve', [InvestorDashboardController::class, 'reserve'])->name('investor.reserve');
    Route::post('/portal/investor/payments/initiate', [InvestorDashboardController::class, 'pay'])->name('investor.pay');
    Route::get('/portal/investor/payments/{payment}/invoice', [InvestorDashboardController::class, 'invoice'])->name('investor.invoice');
});

Route::middleware(['auth', 'portal.role:landowner'])->group(function () {
    Route::get('/portal/landowner', [PortalController::class, 'landownerDashboard'])->name('landowner.dashboard');
    Route::get('/portal/landowner/submissions', [PortalController::class, 'landownerSubmissions'])->name('landowner.submissions');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Real-Time Notification API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/api/notifications', [NotificationApiController::class, 'index'])->name('api.notifications');
    Route::post('/api/notifications/read-all', [NotificationApiController::class, 'markAllRead'])->name('api.notifications.read-all');
    Route::post('/api/notifications/{id}/read', [NotificationApiController::class, 'markRead'])->name('api.notifications.read');
});
Route::get('/api/notifications/stream', [NotificationApiController::class, 'stream'])->name('api.notifications.stream');

/*
|--------------------------------------------------------------------------
| Payment API Routes (bKash, Nagad, Bank Transfer)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::post('/api/payments/bkash', [PaymentApiController::class, 'processBkash'])->name('api.payments.bkash');
    Route::post('/api/payments/nagad', [PaymentApiController::class, 'processNagad'])->name('api.payments.nagad');
    Route::post('/api/payments/bank', [PaymentApiController::class, 'processBank'])->name('api.payments.bank');
});
Route::get('/api/payments/verify/{transactionId}', [PaymentApiController::class, 'verifyStatus'])->name('api.payments.verify');
