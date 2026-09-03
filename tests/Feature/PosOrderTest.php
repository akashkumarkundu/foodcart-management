<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Food;
use App\Models\Order;
use App\Models\User;

test('authenticated user can access POS terminal', function () {
    $user = User::factory()->create(['role' => 'owner']);

    $response = $this->actingAs($user)->get(route('pos.index'));

    $response->assertOk();
    $response->assertSee('Food Cart POS Terminal');
});

test('pos checkout successfully places order and deducts inventory stock', function () {
    $user = User::factory()->create(['role' => 'staff']);

    $category = Category::create([
        'name' => 'Burgers',
        'slug' => 'burgers',
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Beef Cheese Burger',
        'bengali_name' => 'বিফ চিজ বার্গার',
        'slug' => 'beef-cheese-burger',
        'selling_price' => 200.00,
        'cost_price' => 110.00,
        'current_stock' => 50,
        'min_stock' => 5,
        'unit' => 'pcs',
        'is_active' => true,
    ]);

    $payload = [
        'customer_name' => 'Arif Hossain',
        'customer_phone' => '01712334455',
        'payment_method' => 'bkash',
        'transaction_id' => 'BK12345678',
        'paid_amount' => 400.00,
        'items' => [
            ['food_id' => $food->id, 'quantity' => 2],
        ],
    ];

    $response = $this->actingAs($user)->postJson(route('pos.checkout'), $payload);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', [
        'subtotal' => 400.00,
        'total_amount' => 400.00,
        'payment_method' => 'bkash',
        'payment_status' => 'paid',
        'order_status' => 'completed',
    ]);

    $this->assertDatabaseHas('payments', [
        'payment_method' => 'bkash',
        'amount' => 400.00,
        'transaction_id' => 'BK12345678',
    ]);

    // Verify atomic inventory deduction: 50 - 2 = 48
    expect($food->fresh()->current_stock)->toBe(48);
});

test('coupon validation correctly calculates discounts', function () {
    $user = User::factory()->create(['role' => 'staff']);

    Coupon::create([
        'code' => 'DISC50',
        'discount_type' => 'fixed',
        'discount_value' => 50,
        'min_order_amount' => 300,
        'is_active' => true,
    ]);

    // Valid coupon above min threshold
    $validResponse = $this->actingAs($user)->postJson(route('pos.validate-coupon'), [
        'code' => 'DISC50',
        'subtotal' => 350.00,
    ]);

    $validResponse->assertOk();
    $validResponse->assertJson([
        'valid' => true,
        'discount' => 50.00,
    ]);

    // Invalid when below min threshold
    $invalidResponse = $this->actingAs($user)->postJson(route('pos.validate-coupon'), [
        'code' => 'DISC50',
        'subtotal' => 200.00,
    ]);

    $invalidResponse->assertStatus(422);
    $invalidResponse->assertJson([
        'valid' => false,
    ]);
});

test('invoice view displays 80mm thermal format with bdt currency', function () {
    $user = User::factory()->create(['role' => 'owner']);

    $category = Category::create([
        'name' => 'Snacks',
        'slug' => 'snacks',
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Fuchka Platter',
        'slug' => 'fuchka-platter',
        'selling_price' => 80.00,
        'cost_price' => 30.00,
        'current_stock' => 20,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_number' => 'FC-20260901-9999',
        'subtotal' => 80.00,
        'total_amount' => 80.00,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'order_status' => 'completed',
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('orders.invoice', $order));

    $response->assertOk();
    $response->assertSee('FC-20260901-9999');
    $response->assertSee('৳');
    $response->assertSee('FOODCART360');
});
