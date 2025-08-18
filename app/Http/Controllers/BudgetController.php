<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $budgets = Budget::where('user_id', auth()->id())
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get();

        $spendings = Transaction::where('user_id', auth()->id())
            ->where('type', 'pengeluaran')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->select('category', DB::raw('SUM(amount) as total_spent'))
            ->groupBy('category')
            ->pluck('total_spent', 'category');

        return Inertia::render('Budgets/Index', [
            'budgets' => $budgets,
            'spendings' => $spendings,
        ]);
    }

    public function create()
    {
        return Inertia::render('Budgets/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        Budget::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'category' => strtolower($validated['category']),
                'month' => now()->month,
                'year' => now()->year,
            ],
            ['amount' => $validated['amount']]
        );

        return redirect()->route('budgets.index')->with('success', 'Budget berhasil disimpan.');
    }

    public function edit(Budget $budget)
    {
        if ($budget->user_id !== auth()->id()) {
            abort(403);
        }
        return Inertia::render('Budgets/Edit', ['budget' => $budget]);
    }

    public function update(Request $request, Budget $budget)
    {
        if ($budget->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
        ]);

        // Di sini kita update, bukan updateOrCreate
        $budget->update($validated);

        return redirect()->route('budgets.index')->with('success', 'Budget berhasil diperbarui.');
    }

    public function destroy(Budget $budget)
    {
        if ($budget->user_id !== auth()->id()) {
            abort(403);
        }
        $budget->delete();
        return redirect()->route('budgets.index')->with('success', 'Budget berhasil dihapus.');
    }
}
