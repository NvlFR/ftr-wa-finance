<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        // Ambil data transaksi milik user yang login, 15 data per halaman
        $transactions = Transaction::where('user_id', auth()->id())
            ->latest() // Urutkan dari yang terbaru
            ->paginate(15);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions
        ]);
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

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
