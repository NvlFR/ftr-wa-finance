<?php

namespace App\Http\Controllers;

use App\Traits\FinancialSummaryTrait;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestmentController extends Controller
{
    use FinancialSummaryTrait; // Gunakan Trait kita

    public function index()
    {
        $portfolio = $this->getPortfolioSummary(auth()->id());

        return Inertia::render('Investments/Index', [
            'portfolio' => $portfolio
        ]);
    }
}
