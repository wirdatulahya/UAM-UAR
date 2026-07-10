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

    // ── Access Matrix ──────────────────────────────────────────────────────
    // Index (with Role search)
    Route::get('/access-matrix', [AccessMatrixController::class, 'index'])
        ->name('access-matrix.index');
        
    // Role Details (AJAX)
    Route::get('/access-matrix/role-details', [AccessMatrixController::class, 'roleDetails'])
        ->name('access-matrix.role-details');

    // Import Excel
    Route::post('/access-matrix/import', [AccessMatrixController::class, 'import'])
        ->name('access-matrix.import');

    // Clear all records
    Route::delete('/access-matrix/clear', [AccessMatrixController::class, 'clear'])
        ->name('access-matrix.clear');

    // Create new record
    Route::get('/access-matrix/create', [AccessMatrixController::class, 'create'])
        ->name('access-matrix.create');
    Route::post('/access-matrix', [AccessMatrixController::class, 'store'])
        ->name('access-matrix.store');

    // Edit / Update / Delete a single record
    // Note: {uamRecord} matches the UamRecord model via implicit route-model binding
    Route::get('/access-matrix/{uamRecord}/edit', [AccessMatrixController::class, 'edit'])
        ->name('access-matrix.edit');
    Route::put('/access-matrix/{uamRecord}', [AccessMatrixController::class, 'update'])
        ->name('access-matrix.update');
    Route::delete('/access-matrix/{uamRecord}', [AccessMatrixController::class, 'destroy'])
        ->name('access-matrix.destroy');

    // AJAX — role details for Access modal
    Route::get('/access-matrix/role-details', [AccessMatrixController::class, 'roleDetails'])
        ->name('access-matrix.role-details');

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