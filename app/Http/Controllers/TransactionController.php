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

        public function create()
    {
        // Metode ini hanya bertugas menampilkan halaman form pembuatan
        return Inertia::render('Transactions/Create');
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

    // --- TAMBAHKAN METODE BARU INI (Parameter $transaction adalah magic dari Laravel) ---
    public function edit(Transaction $transaction)
    {
        // Pastikan user hanya bisa mengedit transaksinya sendiri (Otorisasi)
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        // Metode ini menampilkan halaman form edit dengan data yang sudah ada
        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction
        ]);
    }

    public function show(Transaction $transaction)
{
    // Otorisasi: Pastikan user hanya bisa melihat transaksinya sendiri
    if ($transaction->user_id !== auth()->id()) {
        abort(403);
    }

    return Inertia::render('Transactions/Show', [
        'transaction' => $transaction
    ]);
}

public function update(Request $request, Transaction $transaction)
{
    // Otorisasi: Pastikan user hanya bisa mengupdate transaksinya sendiri
    if ($transaction->user_id !== auth()->id()) {
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

    return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diperbarui.');
}

public function destroy(Transaction $transaction)
{
    // Otorisasi: Pastikan user hanya bisa menghapus transaksinya sendiri
    if ($transaction->user_id !== auth()->id()) {
        abort(403);
    }

    $transaction->delete();

    return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus.');
}



    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
