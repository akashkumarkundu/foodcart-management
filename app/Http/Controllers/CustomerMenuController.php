<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Food;
use App\Models\Order;
use App\Models\Review;
use App\Models\Setting;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerMenuController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display the digital menu for customers and public visitors.
     */
    public function index(Request $request): View
    {
        $categories = Category::where('is_active', true)
            ->with(['activeFoods.reviews'])
            ->orderBy('sort_order')
            ->get();

        $allActiveFoods = Food::where('is_active', true)
            ->with(['category', 'reviews'])
            ->get();

        // Flash sale foods for Daraz-style deal section
        $flashSaleFoods = $allActiveFoods->take(6)->map(function ($food) {
            $discountPct = 20; // 20% flash discount display
            $originalPrice = round($food->selling_price / (1 - ($discountPct / 100)));
            $food->flash_original_price = $originalPrice;
            $food->flash_discount_pct = $discountPct;
            $food->flash_sold_count = max(8, ($food->id * 7) % 35);
            $food->flash_stock_total = $food->flash_sold_count + max(3, $food->current_stock);

            return $food;
        });

        $cartName = Setting::get('cart_name', 'রেশম নগরী বাইটস (Resham Nogori Bites)');
        $cartPhone = Setting::get('cart_phone', '01712-345678');
        $cartAddress = Setting::get('cart_address', 'রাজশাহী সরকারি মহিলা কলেজ গেট সংলগ্ন, রাজশাহী');
        $bkashNumber = Setting::get('bkash_number', '01712-345678');
        $nagadNumber = Setting::get('nagad_number', '01712-345678');
        $footerText = Setting::get('receipt_footer', 'ধন্যবাদ! রেশম নগরী বাইটসে আবার আসবেন। (Visit Us Again!)');
        $isCartOpen = (bool) Setting::get('is_cart_open', true);
        $recentReviews = Review::where('is_approved', true)
            ->with('food')
            ->latest()
            ->take(8)
            ->get();

        $coupons = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', now());
            })
            ->get();

        return view('welcome', compact(
            'categories',
            'flashSaleFoods',
            'allActiveFoods',
            'coupons',
            'cartName',
            'cartPhone',
            'cartAddress',
            'bkashNumber',
            'nagadNumber',
            'footerText',
            'isCartOpen',
            'recentReviews'
        ));
    }

    /**
     * Handle public customer order placement from the digital menu.
     */
    public function storeOrder(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'order_type' => ['nullable', 'string', 'in:dine_in,takeaway,parcel,counter'],
            'table_no' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['required', 'string', 'in:cash,bkash,nagad,rocket'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.food_id' => ['required', 'exists:foods,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        try {
            $rawOrderType = $validated['order_type'] ?? 'dine_in';
            $normalizedOrderType = in_array($rawOrderType, ['takeaway', 'parcel']) ? 'parcel' : $rawOrderType;

            // Note for kitchen/staff: order type and table
            $orderTypeLabels = [
                'dine_in' => 'বসে খাওয়া (Dine In)',
                'takeaway' => 'পার্সেল / টেকওয়ে (Parcel/Takeaway)',
                'parcel' => 'পার্সেল (Parcel)',
                'counter' => 'কাউন্টার পিকআপ (Counter)',
            ];
            $orderTypeNote = $orderTypeLabels[$rawOrderType] ?? 'বসে খাওয়া';
            $tableNote = ! empty($validated['table_no']) ? " [{$validated['table_no']}]" : '';
            $fullNotes = "অনলাইন কাস্টমার অর্ডার: [{$orderTypeNote}]{$tableNote}";
            if (! empty($validated['notes'])) {
                $fullNotes .= ' - '.$validated['notes'];
            }

            $orderPayload = [
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'order_type' => $normalizedOrderType,
                'coupon_code' => $validated['coupon_code'] ?? null,
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'paid_amount' => 0.00,
                'notes' => $fullNotes,
                'items' => $validated['items'],
            ];

            // Order created as pending for customer
            $order = $this->orderService->createPosOrder($orderPayload, null);
            $order->update([
                'order_status' => 'pending',
                'payment_status' => ($validated['payment_method'] === 'cash') ? 'unpaid' : 'paid',
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে! অর্ডার নম্বর: #{$order->order_number}",
                    'order_number' => $order->order_number,
                    'total_amount' => (float) $order->total_amount,
                    'discount_amount' => (float) $order->discount_amount,
                    'order_status' => $order->order_status,
                ]);
            }

            return redirect()->route('home')
                ->with('success', "আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে! অর্ডার নম্বর: #{$order->order_number} (মোট: ৳{$order->total_amount})");
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Validate and apply a coupon / voucher for the customer cart
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $coupon = Coupon::where('code', trim(strtoupper($validated['code'])))
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            return response()->json([
                'success' => false,
                'message' => 'ভাউচার কোডটি সঠিক নয়। অনুগ্রহ করে পুনরায় চেক করুন।',
            ], 422);
        }

        $subtotal = (float) $validated['subtotal'];

        if (! $coupon->isValidForAmount($subtotal)) {
            return response()->json([
                'success' => false,
                'message' => "এই ভাউচারটি ব্যবহার করতে ন্যূনতম ৳{$coupon->min_order_amount} টাকার খাবার অর্ডার করতে হবে।",
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return response()->json([
            'success' => true,
            'message' => "🎉 ভাউচার সফলভাবে যুক্ত হয়েছে! আপনি ৳{$discount} ছাড় পেয়েছেন।",
            'coupon' => [
                'code' => $coupon->code,
                'discount' => $discount,
                'discount_type' => $coupon->discount_type,
                'discount_value' => (float) $coupon->discount_value,
                'description' => $coupon->description,
            ],
            'new_total' => max(0, $subtotal - $discount),
        ]);
    }

    /**
     * Search and track order status by order number or phone.
     */
    public function track(Request $request): JsonResponse
    {
        $search = trim($request->input('query', ''));
        if (empty($search)) {
            return response()->json(['found' => false, 'message' => 'অনুগ্রহ করে অর্ডার নম্বর বা ফোন নম্বর দিন।'], 400);
        }

        $order = Order::with(['items', 'customer'])
            ->where('order_number', 'like', "%{$search}%")
            ->orWhereHas('customer', function ($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%");
            })
            ->latest()
            ->first();

        if (! $order) {
            return response()->json(['found' => false, 'message' => 'কোনো অর্ডার পাওয়া যায়নি।'], 404);
        }

        return response()->json([
            'found' => true,
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->order_status,
                'status_bn' => match ($order->order_status) {
                    'pending' => 'অপেক্ষারত (Pending)',
                    'preparing' => 'রান্না হচ্ছে (Preparing)',
                    'ready' => 'রেডি আছে! কাউন্টার থেকে নিন (Ready)',
                    'completed' => 'ডেলিভারি সম্পন্ন (Completed)',
                    'cancelled' => 'বাতিল (Cancelled)',
                    default => $order->order_status,
                },
                'step' => match ($order->order_status) {
                    'pending' => 1,
                    'preparing' => 2,
                    'ready' => 3,
                    'completed' => 4,
                    default => 1,
                },
                'total_amount' => (float) $order->total_amount,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'time' => $order->created_at->diffForHumans(),
                'created_at_time' => $order->created_at->format('h:i A'),
                'items_count' => $order->items->count(),
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->food_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ]),
            ],
        ]);
    }

    /**
     * Submit a customer review with star rating
     */
    public function storeReview(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'food_id' => ['nullable', 'exists:foods,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:500'],
        ]);

        $review = Review::create([
            'customer_name' => trim($validated['customer_name']),
            'customer_phone' => ! empty($validated['customer_phone']) ? trim($validated['customer_phone']) : null,
            'food_id' => $validated['food_id'] ?? null,
            'rating' => (int) $validated['rating'],
            'comment' => trim($validated['comment']),
            'is_approved' => true,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'ধন্যবাদ! আপনার মূল্যবান রিভিউটি সফলভাবে গ্রহণ করা হয়েছে।',
                'review' => $review,
            ]);
        }

        return back()->with('success', 'ধন্যবাদ! আপনার মূল্যবান রিভিউটি সফলভাবে গ্রহণ করা হয়েছে।');
    }
}
