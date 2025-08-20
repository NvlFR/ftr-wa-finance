<?php

namespace App\Http\Controllers\Admin;

use App\Models\RecurringTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecurringTransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('App/Admin/RecurringTransactions/Index', [
            'recurring_transactions' => Auth::user()->recurringTransactions()->latest()->paginate(15),
        ]);
    }

    public function create()
    {
        return Inertia::render('App/Admin/RecurringTransactions/Editor');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:pemasukan,pengeluaran',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'day_of_month' => 'required|integer|min:1|max:31',
        ]);

        $request->user()->recurringTransactions()->create($validated);

        return redirect()->route('app.admin.recurring-transactions.index')->with('success', 'Transaksi berulang berhasil ditambahkan.');
    }

    public function edit(RecurringTransaction $recurringTransaction)
    {
        if ($recurringTransaction->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('App/Admin/RecurringTransactions/Editor', ['recurring_transaction' => $recurringTransaction]);
    }

    public function update(Request $request, RecurringTransaction $recurringTransaction)
    {
        if ($recurringTransaction->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'type' => 'required|in:pemasukan,pengeluaran',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'day_of_month' => 'required|integer|min:1|max:31',
        ]);
        $recurringTransaction->update($validated);

        return redirect()->route('app.recurring-transactions.index')->with('success', 'Transaksi berulang berhasil diperbarui.');
    }

    public function destroy(RecurringTransaction $recurringTransaction)
    {
        if ($recurringTransaction->user_id !== Auth::id()) {
            abort(403);
        }
        $recurringTransaction->delete();

        return redirect()->route('app.admin.recurring-transactions.index')->with('success', 'Transaksi berulang berhasil dihapus.');
    }
}
