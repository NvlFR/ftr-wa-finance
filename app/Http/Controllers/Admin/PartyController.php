<?php

namespace App\Http\Controllers\Admin;

use App\Models\Party;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartyController extends Controller
{
    public function index()
    {
        return Inertia::render('App/Admin/Parties/Index', [
            'parties' => Party::where('user_id', Auth::id())->latest()->paginate(15),
        ]);
    }

    public function create()
    {
        return Inertia::render('App/Admin/Parties/Editor');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $request->user()->parties()->create($validated);

        return redirect()->route('app.admin.parties.index')->with('success', 'Pihak baru berhasil ditambahkan.');
    }

    public function edit(Party $party)
    {
        if ($party->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('App/Admin/Parties/Editor', ['party' => $party]);
    }

    public function update(Request $request, Party $party)
    {
        if ($party->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $party->update($validated);

        return redirect()->route('app.admin.parties.index')->with('success', 'Data pihak berhasil diperbarui.');
    }

    public function destroy(Party $party)
    {
        if ($party->user_id !== Auth::id()) {
            abort(403);
        }

        $party->delete();

        return redirect()->route('app.admin.parties.index')->with('success', 'Data pihak berhasil dihapus.');
    }
}
