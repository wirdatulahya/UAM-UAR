<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ChangePasswordController;
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

    // ── Modules Entry Pages ───────────────────────────────────────
    Route::get('/access-matrix/request', [AccessMatrixController::class, 'requestModules'])
        ->name('access-matrix.request.index');
    Route::get('/access-matrix/uam-request', [AccessMatrixController::class, 'acceptModules'])
        ->name('access-matrix.uam-request.index');
    Route::get('/access-matrix/approval', [AccessMatrixController::class, 'approvalLanding'])
        ->name('access-matrix.approval.index');

    
    // ── Accept & Approval Lists ────────────────────────────────────────────────
    Route::get('/access-matrix/uam-request/sap', [AccessMatrixController::class, 'uamRequestList'])
        ->name('access-matrix.uam-request.sap');
    Route::get('/access-matrix/approval/sap', [AccessMatrixController::class, 'approvalList'])
        ->name('access-matrix.approval.sap');

    // ── Request Access Matrix (UAM SAP) ──────────────────────────────────────────────
    Route::get('/access-matrix/request/sap', [AccessMatrixController::class, 'approval'])
        ->name('access-matrix.request.sap');
    Route::post('/access-matrix/request/{uamRequest}/submit', [AccessMatrixController::class, 'submitRequest'])
        ->name('access-matrix.submit');

    Route::post('/access-matrix/approval/{uamRequest}/status', [AccessMatrixController::class, 'updateRequestStatus'])
        ->name('access-matrix.update-status');
    Route::post('/access-matrix/approval/{uamRequest}/decide', [AccessMatrixController::class, 'approveDecision'])
        ->name('access-matrix.approve-decision');
    Route::post('/access-matrix/approval/{uamRequest}/final-decide', [AccessMatrixController::class, 'finalApproveDecision'])
        ->name('access-matrix.final-decide');
    Route::post('/access-matrix/approval/{uamRequest}/auto-save', [AccessMatrixController::class, 'autoSaveDecision'])
        ->name('access-matrix.auto-save');

    // ── Import Excel (from Request UAM page) ──────────────────────────────
    Route::post('/access-matrix/import', [AccessMatrixController::class, 'import'])
        ->name('access-matrix.import');

    // ── Access Matrix - SAP Module ─────────────────────────────────────────
    Route::prefix('access-matrix/sap')->group(function () {

        Route::get('/', [AccessMatrixController::class, 'sap'])
            ->name('access-matrix.sap');

        Route::get('/role-details', [AccessMatrixController::class, 'roleDetails'])
            ->name('access-matrix.role-details');

        Route::post('/update-owners', [AccessMatrixController::class, 'updateOwners'])
            ->name('access-matrix.update-owners');

        Route::delete('/clear', [AccessMatrixController::class, 'clear'])
            ->name('access-matrix.clear');

        Route::get('/create', [AccessMatrixController::class, 'create'])
            ->name('access-matrix.create');

        Route::post('/', [AccessMatrixController::class, 'store'])
            ->name('access-matrix.store');

        Route::get('/{uamRecord}/edit', [AccessMatrixController::class, 'edit'])
            ->name('access-matrix.edit');

        Route::put('/{uamRecord}', [AccessMatrixController::class, 'update'])
            ->name('access-matrix.update');

        Route::delete('/{uamRecord}', [AccessMatrixController::class, 'destroy'])
            ->name('access-matrix.destroy');
    });

    // Profile Settings
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/photo', [\App\Http\Controllers\ProfileController::class, 'updatePhoto'])->name('profile.photo.update');

    // Change Password
    Route::get('/change-password', [ChangePasswordController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');

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