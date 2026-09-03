<?php

use App\Models\Category;
use App\Models\Food;
use App\Models\Order;
use App\Models\User;

test('public digital menu page loads for customers with food items and categories', function () {
    $category = Category::create([
        'name' => 'Fast Food',
        'bengali_name' => 'বার্গার ও ফাস্টফুড',
        'slug' => 'fast-food',
        'is_active' => true,
    ]);

    Food::create([
        'category_id' => $category->id,
        'name' => 'Beef Naga Burger',
        'bengali_name' => 'ঝাল বিফ নাগা বার্গার',
        'selling_price' => 190.00,
        'cost_price' => 105.00,
        'current_stock' => 50,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Beef Naga Burger');
    $response->assertSee('ঝাল বিফ নাগা বার্গার');
    $response->assertSee('190');
});

test('customer can place order directly from the website without login', function () {
    $category = Category::create([
        'name' => 'Tea & Coffee',
        'bengali_name' => 'চা ও কফি',
        'slug' => 'tea-coffee',
        'is_active' => true,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Dhakaiya Malai Cha',
        'bengali_name' => 'মালাই চা',
        'selling_price' => 35.00,
        'cost_price' => 14.00,
        'current_stock' => 100,
        'min_stock' => 10,
        'is_active' => true,
    ]);

    $payload = [
        'customer_name' => 'Kamal Hossain',
        'customer_phone' => '01711223344',
        'order_type' => 'counter',
        'payment_method' => 'cash',
        'items' => [
            ['food_id' => $food->id, 'quantity' => 2],
        ],
    ];

    $response = $this->postJson(route('customer.order'), $payload);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', [
        'total_amount' => 70.00,
        'order_status' => 'pending',
        'payment_method' => 'cash',
    ]);
});

test('owner can customize food price in one tap', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $category = Category::create([
        'name' => 'Noodles',
        'slug' => 'noodles',
        'is_active' => true,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Egg Chowmein',
        'bengali_name' => 'ডিম চাউমিন',
        'selling_price' => 90.00,
        'cost_price' => 40.00,
        'current_stock' => 30,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner)->patchJson(route('foods.update-price', $food), [
        'selling_price' => 110.00,
        'cost_price' => 45.00,
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'food' => [
            'id' => $food->id,
            'selling_price' => 110.00,
            'cost_price' => 45.00,
        ],
    ]);

    expect((float) $food->fresh()->selling_price)->toBe(110.00);
    expect((float) $food->fresh()->cost_price)->toBe(45.00);
});

test('customer can track order status', function () {
    $order = Order::create([
        'order_number' => 'FC-TEST-0001',
        'subtotal' => 150.00,
        'total_amount' => 150.00,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
        'order_status' => 'preparing',
    ]);

    $response = $this->getJson(route('customer.track', ['query' => 'FC-TEST-0001']));

    $response->assertOk();
    $response->assertJson([
        'found' => true,
        'order' => [
            'order_number' => 'FC-TEST-0001',
            'status' => 'preparing',
        ],
    ]);
});
