<?php

namespace App\Http\Controllers;

use App\Traits\FinancialSummaryTrait;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    use FinancialSummaryTrait;

    public function index()
    {
        $userId = auth()->id();

        // Mengambil semua data ringkasan menggunakan Trait
        $netWorthSummary = $this->getNetWorthSummary($userId);
        $cashFlowMonthly = $this->getCashFlowSummary($userId, 'bulan');
        $portfolioSummary = $this->getPortfolioSummary($userId);
        $savingsSummary = $this->getSavingsSummary($userId);
        $debtsSummary = $this->getDebtsSummary($userId);

        // Menyiapkan data untuk dikirim ke frontend
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
        ];

        return Inertia::render('Dashboard', [
            'summary' => $summaryData
        ]);
    }
}
