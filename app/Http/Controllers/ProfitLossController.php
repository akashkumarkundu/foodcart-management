<?php

namespace App\Http\Controllers;

use App\Services\FinancialService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfitLossController extends Controller
{
    public function __construct(
        protected FinancialService $financialService
    ) {}

    public function index(Request $request): View
    {
        $period = $request->input('period', 'monthly'); // daily, weekly, monthly, yearly

        $now = Carbon::now();
        $startDate = null;
        $endDate = null;
        $prevStartDate = null;
        $prevEndDate = null;

        switch ($period) {
            case 'daily':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $prevStartDate = $now->copy()->subDay()->startOfDay();
                $prevEndDate = $now->copy()->subDay()->endOfDay();
                $periodLabel = "Today ({$now->format('d M Y')})";
                $prevPeriodLabel = "Yesterday ({$now->copy()->subDay()->format('d M Y')})";
                break;

            case 'weekly':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $prevStartDate = $now->copy()->subWeek()->startOfWeek();
                $prevEndDate = $now->copy()->subWeek()->endOfWeek();
                $periodLabel = 'This Week ('.$startDate->format('M d').' - '.$endDate->format('M d').')';
                $prevPeriodLabel = 'Last Week ('.$prevStartDate->format('M d').' - '.$prevEndDate->format('M d').')';
                break;

            case 'yearly':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $prevStartDate = $now->copy()->subYear()->startOfYear();
                $prevEndDate = $now->copy()->subYear()->endOfYear();
                $periodLabel = 'Year '.$now->year;
                $prevPeriodLabel = 'Year '.($now->year - 1);
                break;

            case 'monthly':
            default:
                $period = 'monthly';
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $prevStartDate = $now->copy()->subMonth()->startOfMonth();
                $prevEndDate = $now->copy()->subMonth()->endOfMonth();
                $periodLabel = $now->format('F Y');
                $prevPeriodLabel = $now->copy()->subMonth()->format('F Y');
                break;
        }

        $currentSummary = $this->financialService->getSummary($startDate, $endDate);
        $prevSummary = $this->financialService->getSummary($prevStartDate, $prevEndDate);

        // Growth percentages
        $salesGrowth = $prevSummary['completed_sales'] > 0
            ? round((($currentSummary['completed_sales'] - $prevSummary['completed_sales']) / $prevSummary['completed_sales']) * 100, 1)
            : 0;

        $profitGrowth = $prevSummary['net_profit'] > 0
            ? round((($currentSummary['net_profit'] - $prevSummary['net_profit']) / $prevSummary['net_profit']) * 100, 1)
            : 0;

        // Monthly breakdown for trend chart
        $monthlyTrend = $this->financialService->getMonthlyBreakdown($now->year);

        return view('profit-loss.index', compact(
            'period',
            'periodLabel',
            'prevPeriodLabel',
            'currentSummary',
            'prevSummary',
            'salesGrowth',
            'profitGrowth',
            'monthlyTrend'
        ));
    }
}
