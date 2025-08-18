<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\PartyController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// --- GRUP UTAMA UNTUK FITUR-FITUR APLIKASI ---
Route::middleware('auth', 'verified')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute untuk Transaksi
    Route::resource('transactions', TransactionController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    // Rute untuk Investasi
    Route::resource('investments', InvestmentController::class)
        ->except(['show']); // 'show' kita definisikan secara custom
    Route::get('/investments/{assetName}', [InvestmentController::class, 'show'])->name('investments.show');

    // Rute untuk Tabungan
    Route::resource('savings', SavingController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'show']);
    Route::post('/savings/{saving}/add-funds', [SavingController::class, 'addFunds'])->name('savings.addFunds');

    // --- RUTE BARU & PEMBARUAN DI SINI ---
    Route::resource('parties', PartyController::class);
    Route::resource('debts', DebtController::class);

   Route::resource('budgets', BudgetController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->middleware(['auth', 'verified']);

    Route::resource('recurring-transactions', RecurringTransactionController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});


require __DIR__.'/auth.php';
