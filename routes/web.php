<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccessMatrixController;
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
        Route::get('/access-matrix/request', [AccessMatrixController::class, 'requestModules'])->name('access-matrix.request.index');
        Route::get('/access-matrix/request/sap', [AccessMatrixController::class, 'approval'])->name('access-matrix.request.sap');
        Route::post('/access-matrix/request/{uamRequest}/submit', [AccessMatrixController::class, 'submitRequest'])->name('access-matrix.submit');
        Route::post('/access-matrix/request/{uamRequest}/sign', [AccessMatrixController::class, 'signRequest'])->name('access-matrix.sign');
        Route::post('/access-matrix/import', [AccessMatrixController::class, 'import'])->name('access-matrix.import');

        // SAP Write Actions
        Route::prefix('access-matrix/sap')->group(function () {
            Route::post('/update-owners', [AccessMatrixController::class, 'updateOwners'])->name('access-matrix.update-owners');
            Route::delete('/clear', [AccessMatrixController::class, 'clear'])->name('access-matrix.clear');
            Route::get('/create', [AccessMatrixController::class, 'create'])->name('access-matrix.create');
            Route::post('/', [AccessMatrixController::class, 'store'])->name('access-matrix.store');
            Route::delete('/role/{uamRequest}/{role}', [AccessMatrixController::class, 'destroyRole'])->name('access-matrix.destroy-role');
            Route::post('/role/{uamRequest}/{role}/tcode', [AccessMatrixController::class, 'storeTcode'])->name('access-matrix.store-tcode');
            Route::get('/{uamRecord}/edit', [AccessMatrixController::class, 'edit'])->name('access-matrix.edit');
            Route::put('/{uamRecord}', [AccessMatrixController::class, 'update'])->name('access-matrix.update');
            Route::delete('/{uamRecord}', [AccessMatrixController::class, 'destroy'])->name('access-matrix.destroy');
        });
    });

    // ── Accept Module (Manager) ────────────────────────────────────────────────
    Route::middleware(['role:manager'])->group(function () {
        Route::get('/access-matrix/uam-request', [AccessMatrixController::class, 'acceptModules'])->name('access-matrix.uam-request.index');
        Route::get('/access-matrix/uam-request/sap', [AccessMatrixController::class, 'uamRequestList'])->name('access-matrix.uam-request.sap');
        Route::post('/access-matrix/approval/{uamRequest}/status', [AccessMatrixController::class, 'updateRequestStatus'])->name('access-matrix.update-status');
        Route::post('/access-matrix/approval/{uamRequest}/decide', [AccessMatrixController::class, 'approveDecision'])->name('access-matrix.approve-decision');
    });

    // ── Approval Matrix Module (AO / Final Approver) ───────────────────────────
    Route::middleware(['role:ao'])->group(function () {
        Route::get('/access-matrix/approval', [AccessMatrixController::class, 'approvalLanding'])->name('access-matrix.approval.index');
        Route::get('/access-matrix/approval/sap', [AccessMatrixController::class, 'approvalList'])->name('access-matrix.approval.sap');
        Route::post('/access-matrix/approval/{uamRequest}/final-decide', [AccessMatrixController::class, 'finalApproveDecision'])->name('access-matrix.final-decide');
    });

    // ── Shared Actions (Manager & AO) ──────────────────────────────────────────
    Route::middleware(['role:manager,ao'])->group(function () {
        Route::post('/access-matrix/approval/{uamRequest}/auto-save', [AccessMatrixController::class, 'autoSaveDecision'])->name('access-matrix.auto-save');
    });

    // ── Shared Actions (All Roles) ─────────────────────────────────────────────
    Route::get('/access-matrix/request/{uamRequest}/download-excel', [AccessMatrixController::class, 'downloadExcel'])->name('access-matrix.download-excel');
    Route::get('/access-matrix/request/{uamRequest}/download-pdf', [AccessMatrixController::class, 'downloadPdf'])->name('access-matrix.download-pdf');
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
    | Future Modules — add routes here as the application grows
    |--------------------------------------------------------------------------
    | - User Access Review (UAR)
    | - Monitoring
    | - Reports
    |--------------------------------------------------------------------------
    */
});