<?php
// app/Traits/FinancialSummaryTrait.php

namespace App\Traits;

use App\Models\Debt;
use App\Models\Saving;
use App\Models\InvestmentTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

trait FinancialSummaryTrait
{
    public function getNetWorthSummary($userId)
    {
        $liquidAssets = Saving::where('user_id', $userId)->sum('current_amount');
        $investmentCapital = InvestmentTransaction::where('user_id', $userId)
            ->select(DB::raw("SUM(CASE WHEN type = 'beli' THEN total_amount ELSE -total_amount END) as total_capital"))
            ->value('total_capital') ?? 0;
        $receivables = Debt::where('user_id', $userId)->where('type', 'piutang')->where('status', 'belum lunas')->sum('amount');
        $debts = Debt::where('user_id', $userId)->where('type', 'hutang')->where('status', 'belum lunas')->sum('amount');

        $totalAssets = $liquidAssets + $investmentCapital + $receivables;
        $netWorth = $totalAssets - $debts;

        return [
            'total_assets' => $totalAssets,
            'total_debts' => $debts,
            'net_worth' => $netWorth,
        ];
    }

    public function getCashFlowSummary($userId, $period = 'bulan')
    {
        $query = Transaction::where('user_id', $userId);
        switch ($period) {
            case 'hari': $query->whereDate('created_at', today()); break;
            case 'minggu': $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]); break;
            case 'bulan':
            default:
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]); break;
        }
        $transactions = $query->get();

        return [
            'income' => $transactions->where('type', 'pemasukan')->sum('amount'),
            'expense' => $transactions->where('type', 'pengeluaran')->sum('amount'),
        ];
    }

    // app/Traits/FinancialSummaryTrait.php

// ... (metode yang sudah ada) ...

public function getPortfolioSummary($userId)
{
    $assets = InvestmentTransaction::where('user_id', $userId)
        ->select(
            'asset_name',
            'asset_type',
            DB::raw("SUM(CASE WHEN type = 'beli' THEN quantity ELSE -quantity END) as total_quantity"),
            DB::raw("SUM(CASE WHEN type = 'beli' THEN total_amount ELSE -total_amount END) as total_capital")
        )
        ->groupBy('asset_name', 'asset_type')
        ->get();

    // Filter aset yang sudah dijual habis
    return $assets->filter(function ($asset) {
        return $asset->total_quantity > 0.00000001; // Toleransi untuk angka desimal
    });
}

// app/Traits/FinancialSummaryTrait.php

// ... (metode yang sudah ada) ...

public function getSavingsSummary($userId)
{
    return Saving::where('user_id', $userId)
        ->orderBy('is_emergency_fund', 'desc') // Dana Darurat selalu di atas
        ->orderBy('created_at', 'asc')
        ->get();
}

public function getDebtsSummary($userId)
{
    $debts = Debt::where('user_id', $userId)
        ->where('status', 'belum lunas')
        ->orderBy('created_at', 'desc')
        ->get();

    // Memisahkan koleksi menjadi dua: satu untuk hutang, satu untuk piutang
    return [
        'piutang' => $debts->where('type', 'piutang')->values(),
        'hutang' => $debts->where('type', 'hutang')->values(),
    ];
}
}
