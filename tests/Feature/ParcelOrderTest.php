<?php

use App\Models\Category;
use App\Models\Food;
use App\Models\Order;
use App\Services\FinancialService;

test('customers can place a parcel order and it is recorded accurately', function () {
    $category = Category::create([
        'name' => 'Fast Food',
        'slug' => 'fast-food',
        'is_active' => true,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Crispy Chicken Burger',
        'selling_price' => 180.00,
        'cost_price' => 90.00,
        'current_stock' => 50,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    $payload = [
        'customer_name' => 'Tanvir Ahmed',
        'customer_phone' => '01711009988',
        'order_type' => 'takeaway', // should be stored as parcel
        'payment_method' => 'cash',
        'items' => [
            ['food_id' => $food->id, 'quantity' => 2],
        ],
    ];

    $response = $this->postJson(route('customer.order'), $payload);

    $response->assertOk();
    $this->assertDatabaseHas('orders', [
        'order_type' => 'parcel',
        'total_amount' => 360.00,
    ]);
});

test('financial service correctly separates parcel orders from dine-in orders', function () {
    $order1 = Order::create([
        'order_number' => 'FC-PARCEL-001',
        'order_type' => 'parcel',
        'subtotal' => 500.00,
        'total_amount' => 500.00,
        'payment_method' => 'cash',
        'order_status' => 'completed',
        'created_at' => now(),
    ]);

    $order2 = Order::create([
        'order_number' => 'FC-DINEIN-001',
        'order_type' => 'dine_in',
        'subtotal' => 300.00,
        'total_amount' => 300.00,
        'payment_method' => 'bkash',
        'order_status' => 'completed',
        'created_at' => now(),
    ]);

    $financialService = app(FinancialService::class);
    $summary = $financialService->getTodayMetrics();

    expect($summary['parcel_orders'])->toBe(1);
    expect($summary['parcel_sales'])->toBe(500.0);
    expect($summary['dine_in_orders'])->toBe(1);
    expect($summary['dine_in_sales'])->toBe(300.0);
});
