<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWasteRequest;
use App\Models\Food;
use App\Models\Notification;
use App\Models\Waste;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WasteController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $query = Waste::with(['food', 'user'])->latest('date');

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        if ($request->filled('food_id')) {
            $query->where('food_id', $request->food_id);
        }

        $wastes = $query->paginate(15)->withQueryString();
        $foods = Food::where('is_active', true)->orderBy('name')->get();

        $today = Carbon::today();
        $todayWasteCost = (float) Waste::whereDate('date', $today)->sum('estimated_cost');
        $weeklyWasteCost = (float) Waste::whereBetween('date', [$today->copy()->subDays(6)->toDateString(), $today->toDateString()])->sum('estimated_cost');
        $monthlyWasteCost = (float) Waste::whereBetween('date', [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()])->sum('estimated_cost');
        $yearlyWasteCost = (float) Waste::whereBetween('date', [$today->copy()->startOfYear()->toDateString(), $today->toDateString()])->sum('estimated_cost');

        // Highest wasted food analytics
        $highestWastedFoods = DB::table('wastes')
            ->join('foods', 'wastes.food_id', '=', 'foods.id')
            ->select('foods.name', DB::raw('SUM(wastes.estimated_cost) as total_cost'), DB::raw('SUM(wastes.quantity) as total_qty'))
            ->groupBy('foods.name')
            ->orderByDesc('total_cost')
            ->take(5)
            ->get();

        // Highest waste reasons
        $wasteByReasons = Waste::select('reason', DB::raw('SUM(estimated_cost) as total_cost'), DB::raw('COUNT(*) as total_records'))
            ->groupBy('reason')
            ->orderByDesc('total_cost')
            ->get();

        // High waste alert check
        $isWasteHigh = $todayWasteCost > 1500; // Threshold

        return view('wastes.index', compact(
            'wastes',
            'foods',
            'todayWasteCost',
            'weeklyWasteCost',
            'monthlyWasteCost',
            'yearlyWasteCost',
            'highestWastedFoods',
            'wasteByReasons',
            'isWasteHigh'
        ));
    }

    public function store(StoreWasteRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $food = Food::findOrFail($validated['food_id']);

        $quantity = (float) $validated['quantity'];
        $unitCost = (float) $food->cost_price;
        $estimatedCost = isset($validated['estimated_cost']) && $validated['estimated_cost'] > 0
            ? (float) $validated['estimated_cost']
            : ($unitCost * $quantity);

        $waste = Waste::create([
            'food_id' => $food->id,
            'user_id' => $request->user()?->id,
            'quantity' => $quantity,
            'unit' => $validated['unit'] ?? $food->unit,
            'estimated_cost' => $estimatedCost,
            'reason' => $validated['reason'],
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Automatically deduct wasted quantity from inventory
        $this->inventoryService->deductStock(
            food: $food,
            quantity: $quantity,
            type: 'waste',
            reference: $waste,
            user: $request->user(),
            notes: "Waste recorded: {$validated['reason']}"
        );

        // If today's total waste crosses high threshold, trigger notification
        $todayTotal = Waste::whereDate('date', today())->sum('estimated_cost');
        if ($todayTotal > 1500) {
            Notification::create([
                'type' => 'high_waste',
                'title' => '⚠️ High Waste Warning: ৳'.number_format($todayTotal, 2).' today',
                'message' => "Today's food waste has exceeded the threshold. Highest waste today is from {$food->name}.",
                'link' => route('wastes.index'),
                'is_read' => false,
            ]);
        }

        return back()->with('success', "Waste logged for {$food->name} and stock deducted successfully.");
    }

    public function destroy(Waste $waste): RedirectResponse
    {
        $waste->delete();

        return back()->with('success', 'Waste record removed.');
    }
}
