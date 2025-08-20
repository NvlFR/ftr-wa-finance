<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\FinancialSummaryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DebtController extends Controller
{
    use FinancialSummaryTrait;

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        /** @var array $debtsData */
        $debtsData = Auth::user()->debts()
            ->with('party')
            ->where('status', 'belum lunas')
            ->latest()
            ->paginate(15);

        return Inertia::render('App/Admin/Debts/Index', [
            'debts' => $debtsData,
        ]);
    }

    public function create()
    {
        return Inertia::render('App/Admin/Debts/Editor', [
            'parties' => Auth::user()->parties()->orderBy('name')->get(),
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

        return redirect()->route('app.admin.debts.index')->with('success', 'Catatan hutang/piutang berhasil ditambahkan.');
    }

    // Metode edit, update, destroy bisa ditambahkan nanti dengan pola yang sama
}
