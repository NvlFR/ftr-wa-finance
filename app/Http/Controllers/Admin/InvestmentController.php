<?php

namespace App\Http\Controllers\Admin;

use App\Models\InvestmentTransaction;
use App\Traits\FinancialSummaryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as FacadeRequest;
use App\Http\Controllers\Controller;
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

    public function data()
    {
        $query = auth()->user()->investmentTransactions()
            ->when(FacadeRequest::input('search'), function ($query, $search) {
                $query->where('asset_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when(FacadeRequest::input('month'), function ($query, $month) {
                $query->whereMonth('transaction_date', $month);
            })
            ->when(FacadeRequest::input('year'), function ($query, $year) {
                $query->whereYear('transaction_date', $year);
            });

        $sort = FacadeRequest::input('sort') ?? 'transaction_date';
        $order = FacadeRequest::input('order') ?? 'desc';

        $transactions = $query->orderBy($sort, $order)->paginate(FacadeRequest::input('limit', 10));

        return $transactions;
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

    public function show(InvestmentTransaction $investment)
    {
        if ($investment->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('App/Admin/Investments/Detail', [
            'investment' => $investment
        ]);
    }
}
