<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $budgets = Auth::user()->budgets
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get();

        $spendings = Auth::user()->transactions()
            ->where('type', 'pengeluaran')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->select('category', DB::raw('SUM(amount) as total_spent'))
            ->groupBy('category')
            ->pluck('total_spent', 'category');

        return Inertia::render('App/Admin/Budgets/Index', [
            'budgets' => $budgets,
            'spendings' => $spendings,
        ]);
    }

    public function create()
    {
        return Inertia::render('App/Admin/Budgets/Editor');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        Budget::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'category' => strtolower($validated['category']),
                'month' => now()->month,
                'year' => now()->year,
            ],
            ['amount' => $validated['amount']]
        );

        return redirect()->route('app.admin.budgets.index')->with('success', 'Budget berhasil disimpan.');
    }

    public function edit(Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('App/Admin/Budgets/Editor', ['budget' => $budget]);
    }

    public function update(Request $request, Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
        ]);

        $budget->update($validated);

        return redirect()->route('app.admin.budgets.index')->with('success', 'Budget berhasil diperbarui.');
    }

    public function destroy(Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }
        $budget->delete();

        return redirect()->route('app.admin.budgets.index')->with('success', 'Budget berhasil dihapus.');
    }
}
