<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Party; 
use App\Traits\FinancialSummaryTrait;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DebtController extends Controller
{
    use FinancialSummaryTrait;

    public function index()
    {
        $debtsData = auth()->user()->debts()
        ->with('party')
        ->where('status', 'belum lunas')
        ->latest()
        ->paginate(15);

        return Inertia::render('Debts/Index', [
            'debts' => $debtsData,
        ]);
    }

    public function create()
    {
        return Inertia::render('Debts/Create', [
            // Kirim daftar pihak ke form untuk pilihan dropdown
            'parties' => auth()->user()->parties()->orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'party_id' => 'required|exists:parties,id',
            'type' => 'required|in:hutang,piutang',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
        ]);

        $request->user()->debts()->create($validated);

        return redirect()->route('debts.index')->with('success', 'Catatan hutang/piutang berhasil ditambahkan.');
    }

    // Metode edit, update, destroy bisa ditambahkan nanti dengan pola yang sama
}
