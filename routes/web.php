<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\DebtController;
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
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


Route::resource('transactions', TransactionController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'show'])
    ->middleware(['auth', 'verified']);
Route::resource('investments', InvestmentController::class)
    ->except(['show', 'edit', 'update', 'destroy', 'show', ]) // Definisikan semua kecuali yang custom
    ->middleware(['auth', 'verified']);

// Rute custom untuk halaman detail
Route::get('/investments/{assetName}', [InvestmentController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('investments.show');

Route::resource('savings', SavingController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'show'])
    ->middleware(['auth', 'verified']);

Route::post('/savings/{saving}/add-funds', [SavingController::class, 'addFunds'])
    ->middleware(['auth', 'verified'])
    ->name('savings.addFunds');

    Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
});

require __DIR__.'/auth.php';
