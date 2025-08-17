<?php

namespace App\Http\Controllers;

use App\Traits\FinancialSummaryTrait;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DebtController extends Controller
{
    use FinancialSummaryTrait;

    public function index()
    {
        $debtsData = $this->getDebtsSummary(auth()->id());

        return Inertia::render('Debts/Index', [
            'receivables' => $debtsData['piutang'], // Piutang
            'payables' => $debtsData['hutang'],    // Hutang
        ]);
    }
}
