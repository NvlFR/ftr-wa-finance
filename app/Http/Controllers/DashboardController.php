<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Traits\FinancialSummaryTrait; // <-- Gunakan Trait kita
use Illuminate\Http\Request;
use Inertia\Inertia; // <-- Gunakan Inertia

class DashboardController extends Controller
{
    use FinancialSummaryTrait; // <-- Deklarasikan penggunaan Trait

    public function index()
    {
        $userId = auth()->id();

        $netWorthSummary = $this->getNetWorthSummary($userId);
        $cashFlowMonthly = $this->getCashFlowSummary($userId, 'bulan');

        // Menggabungkan semua data untuk dikirim ke frontend
        $summaryData = array_merge($netWorthSummary, [
            'monthly_income' => $cashFlowMonthly['income'],
            'monthly_expense' => $cashFlowMonthly['expense'],
        ]);

        return Inertia::render('Dashboard', [
            'summary' => $summaryData
        ]);
    }
}
