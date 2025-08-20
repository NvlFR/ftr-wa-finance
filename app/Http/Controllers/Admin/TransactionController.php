<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return Inertia::render('App/Admin/Transactions/Index', [
            'transactions' => $transactions,
        ]);
    }

    public function create()
    {
        return Inertia::render('App/Admin/Transactions/Editor');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:pemasukan,pengeluaran',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'created_at' => 'required|date',
        ]);

        $request->user()->transactions()->create($validated);

        return redirect()->route('app.admin.transactions.index')->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('App/Admin/Transactions/Edit', [
            'transaction' => $transaction,
        ]);
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('App/Admin/Transactions/Editor', [
            'transaction' => $transaction,
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|in:pemasukan,pengeluaran',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'created_at' => 'required|date',
        ]);

        $transaction->update($validated);

        return redirect()->route('app.admin.transactions.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $transaction->delete();

        return redirect()->route('app.admin.transactions.index')->with('success', 'Transaksi berhasil dihapus.');
    }



    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
