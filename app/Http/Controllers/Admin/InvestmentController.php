<?php

namespace App\Http\Controllers\Admin;

use App\Models\InvestmentTransaction;
use App\Traits\FinancialSummaryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestmentController extends Controller
{
    use FinancialSummaryTrait; // Gunakan Trait kita

    public function index()
    {
        $portfolio = $this->getPortfolioSummary(Auth::id());

        return Inertia::render('App/Admin/Investments/Index', [
            'portfolio' => $portfolio,
        ]);
    }

    public function create()
    {
        return Inertia::render('App/Admin/Investments/Editor');
    }

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

        return redirect()->route('app.admin.investments.index')->with('success', 'Transaksi investasi berhasil dicatat.');
    }

    public function show(Request $request, $assetName)
    {
        $userId = Auth::id();

        $history = InvestmentTransaction::where('user_id', $userId)
            ->where('asset_name', $assetName)
            ->latest('transaction_date')
            ->paginate(15);

        if ($history->isEmpty()) {
            abort(404);
        }

        $portfolioSummary = $this->getPortfolioSummary($userId);
        $assetSummary = $portfolioSummary->firstWhere('asset_name', $assetName);

        return Inertia::render('App/Admin/Investments/Detail', [
            'asset' => $assetSummary,
            'history' => $history,
        ]);
    }
}
