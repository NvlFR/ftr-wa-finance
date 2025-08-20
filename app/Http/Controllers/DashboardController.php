<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Traits\FinancialSummaryTrait;
use Inertia\Inertia;

class DashboardController extends Controller
{
    use FinancialSummaryTrait;

    public function index()
    {
        $userId = auth()->id();

        // --- Mengambil SEMUA data ringkasan yang dibutuhkan dasbor ---
        $netWorthSummary = $this->getNetWorthSummary($userId);
        $cashFlowMonthly = $this->getCashFlowSummary($userId, 'bulan');
        $portfolioSummary = $this->getPortfolioSummary($userId);
        $savingsSummary = $this->getSavingsSummary($userId);
        $debtsSummary = $this->getDebtsSummary($userId);

        // Khusus untuk total budget bulan ini
        $totalBudget = Budget::where('user_id', $userId)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->sum('amount');

        // --- Menyusun SEMUA data ke dalam satu array ---
        $summaryData = [
            'netWorth' => $netWorthSummary['net_worth'],
            'totalAssets' => $netWorthSummary['total_assets'],
            'totalDebts' => $netWorthSummary['total_debts'],
            'monthlyIncome' => $cashFlowMonthly['income'],
            'monthlyExpense' => $cashFlowMonthly['expense'],
            'totalInvestment' => $portfolioSummary->sum('total_capital'),
            'activeDebts' => $debtsSummary['hutang'],
            'activeReceivables' => $debtsSummary['piutang'],
            'savingsGoals' => $savingsSummary,
            'totalBudget' => $totalBudget,
            'totalSpending' => $cashFlowMonthly['expense'],
        ];

        // --- Mengirim satu array lengkap ke frontend ---
        return Inertia::render('Dashboard', [
            'summary' => $summaryData,
        ]);
    }
}
