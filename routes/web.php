<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- Impor namespace Admin agar lebih rapi ---
use App\Http\Controllers\Admin;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// --- Kelompokkan SEMUA rute yang butuh login di sini ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Rute Utama
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Rute Profil Pengguna
    Route::get('/profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [Admin\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute Fitur Keuangan
    Route::resource('transactions', Admin\TransactionController::class);

    Route::resource('investments', Admin\InvestmentController::class)->except(['show']);
    Route::get('/investments/{assetName}', [Admin\InvestmentController::class, 'show'])->name('investments.show');

    Route::resource('savings', Admin\SavingController::class);
    Route::post('/savings/{saving}/add-funds', [Admin\SavingController::class, 'addFunds'])->name('savings.addFunds');

    Route::resource('parties', Admin\PartyController::class);
    Route::resource('debts', Admin\DebtController::class);
    Route::resource('budgets', Admin\BudgetController::class);
    Route::resource('recurring-transactions', Admin\RecurringTransactionController::class);

    Route::post('/profile/generate-whatsapp-code', [Admin\ProfileController::class, 'generateWhatsappLinkCode'])->name('profile.generateWhatsappLinkCode');
    // ...
});


require __DIR__ . '/auth.php';
