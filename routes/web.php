<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MasterDataController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccessMatrixController;
use App\Http\Controllers\UarController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// ──────────────────────────────────────────────
// Guest Routes (unauthenticated users only)
// ──────────────────────────────────────────────
Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    // Register
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
});

// ──────────────────────────────────────────────
// Authenticated Routes
// ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Modules Entry Pages & Actions (Admin & PIC AO) ──────────────────────
    Route::middleware(['role:admin,pic_ao'])->group(function () {
        Route::post('/access-matrix/copy-baseline', [AccessMatrixController::class, 'copyFromBaseline'])->name('access-matrix.copy-baseline');
        
        // Level 1: Application list (UAM SAP, etc.)
        Route::get('/access-matrix/request', [AccessMatrixController::class, 'requestModules'])->name('access-matrix.request.index');
        Route::post('/access-matrix/applications', [AccessMatrixController::class, 'storeApplication'])->name('access-matrix.application.store');
        Route::delete('/access-matrix/applications/{id}', [AccessMatrixController::class, 'destroyApplication'])->name('access-matrix.application.destroy');
        
        // Level 2: Modules dashboard card selection for Application (PS, FM, etc.)
        Route::get('/access-matrix/request/{app}', [AccessMatrixController::class, 'requestModulesByApp'])->name('access-matrix.request.app');
        Route::post('/access-matrix/request/{app}/modules', [AccessMatrixController::class, 'storeModule'])->name('access-matrix.module.store');
        Route::delete('/access-matrix/modules/{id}', [AccessMatrixController::class, 'destroyModule'])->name('access-matrix.module.destroy');
        
        // Level 3: Request list & upload for specific Module
        Route::get('/access-matrix/request/{app}/{module}', [AccessMatrixController::class, 'approval'])->name('access-matrix.request.module.list');

        Route::post('/access-matrix/request/{uamRequest}/submit', [AccessMatrixController::class, 'submitRequest'])->name('access-matrix.submit');
        Route::post('/access-matrix/request/{uamRequest}/sign', [AccessMatrixController::class, 'signRequest'])->name('access-matrix.sign');
        Route::post('/access-matrix/import', [AccessMatrixController::class, 'import'])->name('access-matrix.import');

        // SAP Write Actions
        Route::prefix('access-matrix/sap')->group(function () {
            Route::post('/update-owners', [AccessMatrixController::class, 'updateOwners'])->name('access-matrix.update-owners');
            Route::delete('/clear', [AccessMatrixController::class, 'clear'])->name('access-matrix.clear');
            Route::get('/create', [AccessMatrixController::class, 'create'])->name('access-matrix.create');
            Route::post('/', [AccessMatrixController::class, 'store'])->name('access-matrix.store');
            Route::delete('/role/{role}', [AccessMatrixController::class, 'destroyRole'])->name('access-matrix.destroy-role');
            Route::post('/role/{role}/tcode', [AccessMatrixController::class, 'storeTcode'])->name('access-matrix.store-tcode');
            Route::get('/{uamRecord}/edit', [AccessMatrixController::class, 'edit'])->name('access-matrix.edit');
            Route::put('/{uamRecord}', [AccessMatrixController::class, 'update'])->name('access-matrix.update');
            Route::delete('/{uamRecord}', [AccessMatrixController::class, 'destroy'])->name('access-matrix.destroy');
        });
    });

    // ── Accept Module (Manager) ────────────────────────────────────────────────
    Route::middleware(['role:manager,admin'])->group(function () {
        Route::get('/access-matrix/uam-request', [AccessMatrixController::class, 'acceptModules'])->name('access-matrix.uam-request.index');
        Route::get('/access-matrix/uam-request/{app}', [AccessMatrixController::class, 'acceptModulesByApp'])->name('access-matrix.uam-request.app');
        Route::get('/access-matrix/uam-request/{app}/{module}', [AccessMatrixController::class, 'uamRequestList'])->name('access-matrix.uam-request.module.list');
    });

    Route::middleware(['role:manager'])->group(function () {
        Route::post('/access-matrix/approval/{uamRequest}/status', [AccessMatrixController::class, 'updateRequestStatus'])->name('access-matrix.update-status');
        Route::post('/access-matrix/approval/{uamRequest}/decide', [AccessMatrixController::class, 'approveDecision'])->name('access-matrix.approve-decision');
    });

    // ── Approval Matrix Module (AO / Final Approver) ───────────────────────────
    Route::middleware(['role:ao,admin'])->group(function () {
        Route::get('/access-matrix/approval', [AccessMatrixController::class, 'approvalLanding'])->name('access-matrix.approval.index');
        Route::get('/access-matrix/approval/{app}', [AccessMatrixController::class, 'approvalLandingByApp'])->name('access-matrix.approval.app');
        Route::get('/access-matrix/approval/{app}/{module}', [AccessMatrixController::class, 'approvalList'])->name('access-matrix.approval.module.list');
    });

    Route::middleware(['role:ao'])->group(function () {
        Route::post('/access-matrix/approval/{uamRequest}/final-decide', [AccessMatrixController::class, 'finalApproveDecision'])->name('access-matrix.final-decide');
    });

    // ── Shared Actions (Manager & AO) ──────────────────────────────────────────
    Route::middleware(['role:manager,ao'])->group(function () {
        Route::post('/access-matrix/approval/{uamRequest}/auto-save', [AccessMatrixController::class, 'autoSaveDecision'])->name('access-matrix.auto-save');
    });

    // ── Shared Actions (All Roles) ─────────────────────────────────────────────
    Route::get('/access-matrix/request/{uamRequest}/download-excel', [AccessMatrixController::class, 'downloadExcel'])->name('access-matrix.download-excel');
    Route::get('/access-matrix/request/{uamRequest}/download-pdf', [AccessMatrixController::class, 'downloadPdf'])->name('access-matrix.download-pdf');
    Route::get('/access-matrix/request/{uamRequest}/preview-pdf', [AccessMatrixController::class, 'previewPdf'])->name('access-matrix.preview-pdf');
    Route::get('/access-matrix/request/{uamRequest}/history', [AccessMatrixController::class, 'versionHistory'])->name('access-matrix.history');
    Route::get('/access-matrix/request/{uamRequest}/matrix-map', [AccessMatrixController::class, 'getMatrixMap'])->name('access-matrix.matrix-map');
    Route::get('/access-matrix/sap', [AccessMatrixController::class, 'sap'])->name('access-matrix.sap');
    Route::get('/access-matrix/sap/role-details', [AccessMatrixController::class, 'roleDetails'])->name('access-matrix.role-details');

    // Profile Settings
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/photo', [\App\Http\Controllers\ProfileController::class, 'updatePhoto'])->name('profile.photo.update');

    // Notifications
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all');


    /*
    |--------------------------------------------------------------------------
    | User Management & Master Data (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->group(function () {
        // ── Master Data ──────────────────────────────────────────────────────
        Route::prefix('master-data')->name('master-data.')->group(function () {
            // BPO
            Route::get('/bpo',              [MasterDataController::class, 'bpoIndex'])  ->name('bpo');
            Route::post('/bpo',             [MasterDataController::class, 'bpoStore'])  ->name('bpo.store');
            Route::put('/bpo/{bpo}',        [MasterDataController::class, 'bpoUpdate']) ->name('bpo.update');
            Route::delete('/bpo/{bpo}',     [MasterDataController::class, 'bpoDestroy'])->name('bpo.destroy');

            // Unit
            Route::get('/unit',             [MasterDataController::class, 'unitIndex'])   ->name('unit');
            Route::post('/unit',            [MasterDataController::class, 'unitStore'])   ->name('unit.store');
            Route::put('/unit/{unit}',      [MasterDataController::class, 'unitUpdate'])  ->name('unit.update');
            Route::delete('/unit/{unit}',   [MasterDataController::class, 'unitDestroy']) ->name('unit.destroy');

            // User
            Route::get('/user',             [MasterDataController::class, 'userIndex'])   ->name('user');
            Route::post('/user',            [MasterDataController::class, 'userStore'])   ->name('user.store');
            Route::put('/user/{user}',      [MasterDataController::class, 'userUpdate'])  ->name('user.update');
            Route::delete('/user/{user}',   [MasterDataController::class, 'userDestroy']) ->name('user.destroy');
        });

        // ── User Management ──────────────────────────────────────────────────
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    // ── Master Data JSON API (authenticated, any role) ───────────────────────
    // Used by Add Role dropdown; protected behind auth only (no admin req)
    Route::prefix('api/master-data')->name('api.master-data.')->group(function () {
        Route::get('/bpos',              [MasterDataController::class, 'apiBpos'])  ->name('bpos');
        Route::get('/bpos/{bpo}/units',  [MasterDataController::class, 'apiUnits']) ->name('units');
        Route::get('/users',             [MasterDataController::class, 'apiUsers']) ->name('users');
    });

    // ── User Access Review (UAR) Module ──────────────────────────────
    Route::prefix('uar')->name('uar.')->group(function () {
        // Level 1: Application list (UAR SAP, + Add New UAR, etc.)
        Route::get('/',                          [UarController::class, 'index'])->name('index');
        Route::post('/applications',             [UarController::class, 'storeApplication'])->name('application.store');
        Route::delete('/applications/{id}',      [UarController::class, 'destroyApplication'])->name('application.destroy');

        // Level 2: Modules table directory for Application (FM, PS, FI, CO, etc.)
        Route::get('/app/{app}',                 [UarController::class, 'appModules'])->name('app');
        Route::post('/app/{app}/modules',        [UarController::class, 'storeModule'])->name('module.store');
        Route::delete('/modules/{id}',           [UarController::class, 'destroyModule'])->name('module.destroy');

        // Level 3: Session list & upload for specific Module
        Route::get('/app/{app}/{module}',        [UarController::class, 'moduleSessions'])->name('module.sessions');

        // Actions & Imports
        Route::get('/create',                    [UarController::class, 'create'])->name('create');
        Route::post('/import',                   [UarController::class, 'import'])->name('import');
        Route::post('/import-multi',             [UarController::class, 'importMulti'])->name('import-multi');
        Route::get('/session/{uarSession}',      [UarController::class, 'show'])->name('session.show');
        Route::get('/{uarSession}',              [UarController::class, 'show'])->whereNumber('uarSession')->name('show');
        Route::post('/{uarSession}/bulk-accept', [UarController::class, 'bulkAccept'])->name('bulk-accept');
        Route::post('/{uarSession}/complete',    [UarController::class, 'complete'])->name('complete');
        Route::get('/{uarSession}/export-excel', [UarController::class, 'exportExcel'])->name('export-excel');
        Route::get('/{uarSession}/export-pdf',   [UarController::class, 'exportPdf'])->name('export-pdf');
        Route::delete('/{uarSession}',           [UarController::class, 'destroy'])->name('destroy');
        Route::post('/{uarSession}/role-review',     [UarController::class, 'updateRoleReview'])->name('role-review');
        Route::post('/{uarSession}/employee-review', [UarController::class, 'updateEmployeeReview'])->name('employee-review');
        Route::post('/record/{record}/update',   [UarController::class, 'updateRecord'])->name('record.update');
    });
});