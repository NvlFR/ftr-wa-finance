<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\Transaction; // <-- Import Transaction
use App\Traits\FinancialSummaryTrait;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SavingController extends Controller
{
    use FinancialSummaryTrait;

    public function index()
    {
        $savings = $this->getSavingsSummary(auth()->id());

        return Inertia::render('App/Admin/Savings/Index', ['savings' => $savings]);
    }

    public function create()
    {
        return Inertia::render('App/Admin/Savings/Editor');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'goal_name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
        ]);

        $request->user()->savings()->create($validated);

        return redirect()->route('app.admin.savings.index')->with('success', 'Tujuan tabungan berhasil dibuat.');
    }

    public function edit(Saving $saving)
    {
        // Otorisasi
        if ($saving->user_id !== auth()->id() || $saving->is_emergency_fund) {
            abort(403); // Dana darurat tidak boleh diedit dari sini
        }

        return Inertia::render('App/Admin/Savings/Editor', ['saving' => $saving]);
    }

    public function update(Request $request, Saving $saving)
    {
        // Otorisasi
        if ($saving->user_id !== auth()->id() || $saving->is_emergency_fund) {
            abort(403);
        }
        $validated = $request->validate([
            'goal_name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
        ]);
        $saving->update($validated);

        return redirect()->route('app.admin.savings.index')->with('success', 'Tujuan tabungan berhasil diperbarui.');
    }

    public function destroy(Saving $saving)
    {
        // Otorisasi
        if ($saving->user_id !== auth()->id() || $saving->is_emergency_fund) {
            abort(403);
        }
        $saving->delete();

        return redirect()->route('app.admin.savings.index')->with('success', 'Tujuan tabungan berhasil dihapus.');
    }

    public function addFunds(Request $request, Saving $saving)
    {
        if ($saving->user_id !== auth()->id()) {
            abort(403);
        }
        $validated = $request->validate(['amount' => 'required|numeric|min:1']);

        Transaction::create([
            'user_id' => auth()->id(),
            'type' => 'pengeluaran',
            'amount' => $validated['amount'],
            'description' => "Menabung untuk {$saving->goal_name}",
            'category' => $saving->is_emergency_fund ? 'dana darurat' : 'tabungan',
        ]);

        $saving->current_amount += $validated['amount'];
        if ($saving->current_amount >= $saving->target_amount && !$saving->is_emergency_fund) {
            $saving->status = 'completed';
        }
        $saving->save();

        return redirect()->back()->with('success', 'Dana berhasil ditambahkan.');
    }
    public function show(Saving $saving)
    {
        if ($saving->user_id !== auth()->id()) {
            abort(403);
        }

        $history = Transaction::where('user_id', auth()->id())
            ->where(function ($query) use ($saving) {
                $query->where('description', 'like', "%{$saving->goal_name}%")
                      ->orWhere('category', $saving->is_emergency_fund ? 'dana darurat' : 'tabungan');
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('App/Admin/Savings/Detail', [
            'saving' => $saving,
            'history' => $history,
        ]);
    }
}
