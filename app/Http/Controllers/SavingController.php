<?php

namespace App\Http\Controllers;

use App\Traits\FinancialSummaryTrait;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SavingController extends Controller
{
    use FinancialSummaryTrait; // Gunakan Trait kita

    public function index()
    {
        $savings = $this->getSavingsSummary(auth()->id());

        return Inertia::render('Savings/Index', [
            'savings' => $savings
        ]);
    }
}
