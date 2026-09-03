<?php

namespace App\Http\Controllers;

use App\Services\FinancialService;
use App\Services\SmartInsightsService;
use Illuminate\View\View;

class InsightsController extends Controller
{
    public function __construct(
        protected SmartInsightsService $insightsService,
        protected FinancialService $financialService
    ) {}

    public function index(): View
    {
        $insights = $this->insightsService->generateInsights();
        $topFoods = $this->financialService->getTopSellingFoods(8);
        $hourlySales = $this->financialService->getTodayHourlySales();

        return view('insights.index', compact('insights', 'topFoods', 'hourlySales'));
    }
}
