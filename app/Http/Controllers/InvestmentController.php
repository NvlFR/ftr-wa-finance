<?php

namespace App\Http\Controllers;

use App\Traits\FinancialSummaryTrait;
use App\Models\InvestmentTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestmentController extends Controller
{
    use FinancialSummaryTrait; // Gunakan Trait kita

    public function index()
    {
        $portfolio = $this->getPortfolioSummary(auth()->id());

        return Inertia::render('Investments/Index', [
            'portfolio' => $portfolio
        ]);
    }

     public function create()
    {
        return Inertia::render('Investments/Create');
    }

    // --- TAMBAHKAN METODE BARU INI ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:beli,jual',
            'asset_name' => 'required|string|max:255',
            'asset_type' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
        ]);

        $validated['total_amount'] = $validated['quantity'] * $validated['price_per_unit'];

        $request->user()->investmentTransactions()->create($validated);

        return redirect()->route('investments.index')->with('success', 'Transaksi investasi berhasil dicatat.');
    }

    public function show(Request $request, $assetName)
{
    $userId = auth()->id();

    // Ambil semua transaksi untuk aset spesifik ini
    $history = InvestmentTransaction::where('user_id', $userId)
        ->where('asset_name', $assetName)
        ->latest('transaction_date')
        ->paginate(15);

    // Jika tidak ada histori, kemungkinan pengguna mencoba URL yang salah
    if ($history->isEmpty()) {
        abort(404);
    }

    // Ambil data ringkasan untuk aset ini menggunakan Trait
    $portfolioSummary = $this->getPortfolioSummary($userId);
    $assetSummary = $portfolioSummary->firstWhere('asset_name', $assetName);

    return Inertia::render('Investments/Show', [
        'asset' => $assetSummary,
        'history' => $history,
    ]);
}
}
