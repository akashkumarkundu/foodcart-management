<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Food;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): View
    {
        $categories = Category::where('is_active', true)
            ->with(['activeFoods'])
            ->orderBy('sort_order')
            ->get();

        $allFoods = Food::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        $customers = Customer::orderBy('name')->take(50)->get();

        $activeCoupons = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()->toDateString());
            })
            ->get();

        $recentOrders = Order::with(['customer', 'items'])
            ->latest()
            ->take(5)
            ->get();

        return view('pos.index', compact('categories', 'allFoods', 'customers', 'activeCoupons', 'recentOrders'));
    }

    public function store(StoreOrderRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $order = $this->orderService->createPosOrder(
                $request->validated(),
                $request->user()
            );

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Order #{$order->order_number} created successfully!",
                    'order' => $order,
                    'invoice_url' => route('orders.invoice', $order),
                ]);
            }

            return redirect()->route('orders.show', $order)
                ->with('success', "Order #{$order->order_number} completed successfully!");
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * API to validate coupon code
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $code = trim(strtoupper($request->input('code', '')));
        $subtotal = (float) $request->input('subtotal', 0);

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid coupon code.',
            ], 404);
        }

        $error = null;
        if (! $coupon->isValidForAmount($subtotal, $error)) {
            return response()->json([
                'valid' => false,
                'message' => $error,
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return response()->json([
            'valid' => true,
            'discount' => $discount,
            'message' => "Coupon applied: ৳{$discount} discount!",
        ]);
    }
}
