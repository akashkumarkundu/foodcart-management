<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Food;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create POS Order with items, payment, inventory deduction, and customer updates
     *
     * @param  array  $data  Structure:
     *                       - customer_id: ?int
     *                       - customer_name: ?string (if new customer)
     *                       - customer_phone: ?string
     *                       - coupon_code: ?string
     *                       - payment_method: string (cash, bkash, nagad, rocket, card)
     *                       - transaction_id: ?string
     *                       - payment_reference: ?string
     *                       - paid_amount: ?float
     *                       - notes: ?string
     *                       - items: array of ['food_id' => int, 'quantity' => int, 'notes' => ?string]
     */
    public function createPosOrder(array $data, ?User $user = null): Order
    {
        if (empty($data['items'])) {
            throw new Exception('Cart is empty. Please add items to place an order.');
        }

        return DB::transaction(function () use ($data, $user) {
            // 1. Resolve or create customer if phone is provided
            $customer = null;
            if (! empty($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
            } elseif (! empty($data['customer_phone'])) {
                $customer = Customer::firstOrCreate(
                    ['phone' => trim($data['customer_phone'])],
                    ['name' => ! empty($data['customer_name']) ? trim($data['customer_name']) : 'Guest Customer']
                );
            }

            // 2. Calculate items subtotal and profit
            $subtotal = 0.0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $food = Food::findOrFail($item['food_id']);
                $qty = max(1, (int) $item['quantity']);
                $unitPrice = (float) $food->selling_price;
                $costPrice = (float) $food->cost_price;
                $lineTotal = $unitPrice * $qty;
                $lineProfit = ($unitPrice - $costPrice) * $qty;

                $subtotal += $lineTotal;

                $itemsData[] = [
                    'food' => $food,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'cost_price' => $costPrice,
                    'subtotal' => $lineTotal,
                    'profit' => $lineProfit,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            // 3. Apply coupon if provided
            $coupon = null;
            $discountAmount = 0.0;
            if (! empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', trim(strtoupper($data['coupon_code'])))->first();
                if ($coupon && $coupon->isValidForAmount($subtotal)) {
                    $discountAmount = $coupon->calculateDiscount($subtotal);
                    $coupon->increment('times_used');
                }
            }

            // Optional tax calculation (default 0% unless enabled in settings)
            $taxRate = (float) Setting::get('tax_rate', 0.0);
            $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
            $totalAmount = max(0, ($subtotal - $discountAmount) + $taxAmount);

            // 4. Create Order
            $orderNumber = Order::generateOrderNumber();
            $paymentMethod = $data['payment_method'] ?? 'cash';

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer?->id,
                'order_type' => $data['order_type'] ?? 'dine_in',
                'user_id' => $user?->id,
                'coupon_id' => $coupon?->id,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => ($data['payment_method'] ?? 'cash') === 'cash' && ($data['paid_amount'] ?? null) === null ? 'paid' : ($data['payment_status'] ?? 'paid'),
                'order_status' => $data['order_status'] ?? 'completed',
                'notes' => $data['notes'] ?? null,
                'completed_at' => ($data['order_status'] ?? 'completed') === 'completed' ? now() : null,
            ]);

            // 5. Create Order Items & Deduct Inventory
            foreach ($itemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'food_id' => $item['food']->id,
                    'food_name' => $item['food']->name,
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $item['cost_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                    'profit' => $item['profit'],
                    'notes' => $item['notes'],
                ]);

                // Atomic stock deduction
                $this->inventoryService->deductStock(
                    food: $item['food'],
                    quantity: $item['quantity'],
                    type: 'sale',
                    reference: $order,
                    user: $user,
                    notes: "Sold via Order #{$order->order_number}"
                );
            }

            // 6. Record Payment
            $paidAmount = isset($data['paid_amount']) && $data['paid_amount'] > 0
                ? (float) $data['paid_amount']
                : $totalAmount;

            Payment::create([
                'order_id' => $order->id,
                'customer_id' => $customer?->id,
                'payment_method' => $paymentMethod,
                'amount' => $paidAmount,
                'transaction_id' => $data['transaction_id'] ?? null,
                'reference' => $data['payment_reference'] ?? null,
                'status' => 'completed',
                'payment_date' => now(),
                'notes' => $data['payment_notes'] ?? null,
            ]);

            // 7. Update Customer Statistics & Loyalty Points (৳100 = 1 point)
            if ($customer) {
                $customer->increment('total_orders');
                $customer->increment('total_spent', $totalAmount);
                $customer->update(['last_order_at' => now()]);

                // Default: 1 point per ৳100 spent
                $pointRatio = (float) Setting::get('loyalty_points_ratio', 100.0);
                if ($pointRatio > 0) {
                    $earnedPoints = (int) floor($totalAmount / $pointRatio);
                    if ($earnedPoints > 0) {
                        $customer->increment('loyalty_points', $earnedPoints);

                        LoyaltyPoint::create([
                            'customer_id' => $customer->id,
                            'order_id' => $order->id,
                            'points' => $earnedPoints,
                            'type' => 'earned',
                            'description' => "Earned {$earnedPoints} points from Order #{$order->order_number}",
                        ]);
                    }
                }
            }

            return $order->load(['items.food', 'customer', 'payments']);
        });
    }
}
