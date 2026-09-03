<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Food;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Waste;
use App\Services\DailyClosingService;
use App\Services\FinancialService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartBoyController extends Controller
{
    public function __construct(
        protected FinancialService $financialService,
        protected DailyClosingService $closingService
    ) {}

    /**
     * Display the dedicated Cart Boy & Shift workspace.
     * Contains Live Kitchen Orders, POS, Item-wise Sales Timeline,
     * Payment Breakdown, Parcel Customer List, Waste Logging, and Shift Close.
     */
    public function index(Request $request): View
    {
        $categories = Category::where('is_active', true)
            ->with(['activeFoods'])
            ->orderBy('sort_order')
            ->get();

        $foods = Food::where('is_active', true)
            ->with('category')
            ->orderBy('category_id')
            ->get();

        $selectedDate = $request->filled('date') ? Carbon::parse($request->input('date')) : Carbon::today();
        $isToday = $selectedDate->isToday();

        // Today's live active orders for kitchen processing
        $liveOrders = Order::whereDate('created_at', Carbon::today())
            ->whereIn('order_status', ['pending', 'preparing', 'ready'])
            ->with(['customer', 'items'])
            ->latest()
            ->get();

        // Orders on selected date
        $todayOrders = Order::whereDate('created_at', $selectedDate)
            ->with(['customer', 'items'])
            ->latest()
            ->take(50)
            ->get();

        // Recent completed orders count
        $completedTodayCount = Order::whereDate('created_at', $selectedDate)
            ->where('order_status', 'completed')
            ->count();

        // Stock status
        $stockItems = Food::where('is_active', true)
            ->orderBy('current_stock')
            ->get();

        $isOwner = $request->user()->isOwner();

        // Financial & Shift Metrics for Cart Boy & Owner on selected date
        $summary = $this->financialService->getSummary($selectedDate, $selectedDate);
        $salesTimeline = $this->financialService->getSalesTimeline($selectedDate, $selectedDate);
        $itemWiseSales = $this->financialService->getItemWiseSales($selectedDate, $selectedDate);

        // Parcel orders on selected date ("কারা পার্সেল নিলো")
        $parcelOrders = Order::whereDate('created_at', $selectedDate)
            ->whereIn('order_type', ['parcel', 'takeaway'])
            ->with(['customer', 'items'])
            ->latest()
            ->get();

        // Wastes logged on selected date
        $todayWastes = Waste::whereDate('date', $selectedDate)
            ->with('food')
            ->latest()
            ->get();

        $isCartOpen = (bool) Setting::get('is_cart_open', true);
        $cartRent = (float) Setting::get('daily_cart_rent', 0.0);
        $closingPreview = $this->closingService->getClosingPreview($selectedDate);
        $latestOrderId = (int) (Order::max('id') ?? 0);

        return view('cartboy.index', compact(
            'categories',
            'foods',
            'liveOrders',
            'todayOrders',
            'completedTodayCount',
            'stockItems',
            'isOwner',
            'summary',
            'salesTimeline',
            'itemWiseSales',
            'parcelOrders',
            'todayWastes',
            'isCartOpen',
            'cartRent',
            'closingPreview',
            'selectedDate',
            'isToday',
            'latestOrderId'
        ));
    }

    /**
     * 1-Tap Order Status Update for Cart Boy / Cook
     * (Pending -> Preparing -> Ready -> Completed)
     */
    public function updateOrderStatus(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => ['required', 'string', 'in:pending,preparing,ready,completed,cancelled'],
        ]);

        $updateData = ['order_status' => $validated['order_status']];

        if ($validated['order_status'] === 'completed') {
            $updateData['completed_at'] = now();
            if ($order->payment_status === 'unpaid') {
                $updateData['payment_status'] = 'paid';
            }
        }

        $order->update($updateData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "অর্ডার #{$order->order_number} এর স্ট্যাটাস আপডেট করা হয়েছে!",
                'order_status' => $order->order_status,
                'order_id' => $order->id,
            ]);
        }

        return back()->with('success', "অর্ডার #{$order->order_number} এর স্ট্যাটাস আপডেট করা হয়েছে!");
    }

    /**
     * Return live orders JSON for real-time kitchen polling and audio notification.
     */
    public function liveOrdersJson(Request $request): JsonResponse
    {
        $liveOrders = Order::whereDate('created_at', Carbon::today())
            ->whereIn('order_status', ['pending', 'preparing', 'ready'])
            ->with(['customer', 'items'])
            ->latest()
            ->get();

        $latestOrder = Order::whereDate('created_at', Carbon::today())->latest()->first();

        $completedTodayCount = Order::whereDate('created_at', Carbon::today())
            ->where('order_status', 'completed')
            ->count();

        $formattedOrders = $liveOrders->map(function ($o) {
            return [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer_name' => $o->customer?->name ?? 'গেস্ট কাস্টমার',
                'customer_phone' => $o->customer?->phone ?? '',
                'created_time' => $o->created_at->format('h:i A'),
                'time_diff' => $o->created_at->diffForHumans(),
                'order_status' => $o->order_status,
                'order_type' => $o->order_type,
                'table_no' => $o->table_no ?? '',
                'payment_method' => $o->payment_method,
                'payment_status' => $o->payment_status,
                'transaction_id' => $o->transaction_id ?? '',
                'status_bn' => match ($o->order_status) {
                    'pending' => 'অপেক্ষারত',
                    'preparing' => 'রান্না হচ্ছে',
                    'ready' => 'রেডি আছে',
                    default => $o->order_status,
                },
                'total_amount' => (float) $o->total_amount,
                'notes' => $o->notes ?? '',
                'items' => $o->items->map(fn ($item) => [
                    'id' => $item->id,
                    'food_name' => $item->food_name,
                    'quantity' => $item->quantity,
                ]),
            ];
        });

        $summary = $this->financialService->getSummary(Carbon::today(), Carbon::today());
        $itemWiseSales = $this->financialService->getItemWiseSales(Carbon::today(), Carbon::today());

        return response()->json([
            'success' => true,
            'live_orders' => $formattedOrders,
            'latest_order_id' => $latestOrder?->id ?? 0,
            'latest_order' => $latestOrder ? [
                'id' => $latestOrder->id,
                'order_number' => $latestOrder->order_number,
                'customer_name' => $latestOrder->customer?->name ?? 'গেস্ট কাস্টমার',
                'order_type' => $latestOrder->order_type,
                'table_no' => $latestOrder->table_no ?? '',
                'total_amount' => (float) $latestOrder->total_amount,
                'items_summary' => $latestOrder->items->map(fn ($i) => "{$i->quantity}x {$i->food_name}")->join(', '),
            ] : null,
            'pending_count' => $liveOrders->where('order_status', 'pending')->count(),
            'live_count' => $liveOrders->count(),
            'completed_today_count' => $completedTodayCount,
            'summary' => [
                'completed_sales' => (float) $summary['completed_sales'],
                'total_orders' => (int) $summary['total_orders'],
                'cash' => (float) $summary['payment_breakdown']['cash'],
                'digital' => (float) ($summary['payment_breakdown']['bkash'] + $summary['payment_breakdown']['nagad'] + $summary['payment_breakdown']['rocket'] + $summary['payment_breakdown']['card']),
                'parcel_orders' => (int) $summary['parcel_orders'],
                'parcel_sales' => (float) $summary['parcel_sales'],
                'total_waste' => (float) $summary['total_waste'],
                'net_profit' => (float) $summary['net_profit'],
            ],
            'item_wise_sales' => $itemWiseSales->take(10)->values()->all(),
        ]);
    }
}
