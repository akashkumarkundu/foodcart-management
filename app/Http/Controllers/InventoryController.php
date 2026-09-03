<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\InventoryTransaction;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $foodsQuery = Food::with('category');

        if ($request->filled('low_stock')) {
            $foodsQuery->lowStock();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $foodsQuery->where('name', 'like', "%{$search}%")
                ->orWhere('bengali_name', 'like', "%{$search}%");
        }

        $foods = $foodsQuery->orderBy('current_stock', 'asc')->paginate(15)->withQueryString();

        $recentTransactions = InventoryTransaction::with(['food', 'user'])
            ->latest()
            ->take(15)
            ->get();

        $totalItemsCount = Food::count();
        $lowStockCount = Food::lowStock()->count();
        $outOfStockCount = Food::where('current_stock', '<=', 0)->count();

        return view('inventory.index', compact(
            'foods',
            'recentTransactions',
            'totalItemsCount',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    public function adjust(Request $request, Food $food): RedirectResponse
    {
        $request->validate([
            'current_stock' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->inventoryService->adjustStock(
            $food,
            (float) $request->current_stock,
            $request->user(),
            $request->notes
        );

        return back()->with('success', "Stock for {$food->name} adjusted to {$request->current_stock} {$food->unit}(s).");
    }
}
