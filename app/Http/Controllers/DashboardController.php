<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Food;
use App\Models\Order;
use App\Models\Waste;
use App\Services\FinancialService;
use App\Services\SmartInsightsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected FinancialService $financialService,
        protected SmartInsightsService $insightsService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        if (! $request->user()->isOwner()) {
            return redirect()->route('cartboy.index');
        }

        $todayMetrics = $this->financialService->getTodayMetrics();
        $last7Days = $this->financialService->getLast7DaysTrend();
        $hourlySales = $this->financialService->getTodayHourlySales();
        $topFoods = $this->financialService->getTopSellingFoods(5);
        $insights = $this->insightsService->generateInsights();

        // Active foods for quick price, waste, and inventory actions
        $activeFoods = Food::with('category')->where('is_active', true)->orderBy('name')->get();
        $expenseCategories = ExpenseCategory::orderBy('name')->get();

        // Low stock items
        $lowStockFoods = Food::with('category')
            ->lowStock()
            ->where('is_active', true)
            ->take(6)
            ->get();

        // Recent orders
        $recentOrders = Order::with(['customer', 'items'])
            ->latest()
            ->take(6)
            ->get();

        // Recent expenses
        $recentExpenses = Expense::with('category')
            ->latest('date')
            ->take(5)
            ->get();

        // Recent waste
        $recentWastes = Waste::with('food')
            ->latest('date')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'todayMetrics',
            'last7Days',
            'hourlySales',
            'topFoods',
            'insights',
            'activeFoods',
            'expenseCategories',
            'lowStockFoods',
            'recentOrders',
            'recentExpenses',
            'recentWastes'
        ));
    }
}
