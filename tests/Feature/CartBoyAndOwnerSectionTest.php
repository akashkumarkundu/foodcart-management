<?php

use App\Models\Category;
use App\Models\Food;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

test('public customer can place an order successfully without auth or csrf error', function () {
    $category = Category::create([
        'name' => 'Fast Food',
        'bengali_name' => 'ফাস্টফুড',
        'slug' => 'fast-food-test',
        'is_active' => true,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Crispy Chicken Burger',
        'bengali_name' => 'চিকেন বার্গার',
        'selling_price' => 170.00,
        'cost_price' => 95.00,
        'current_stock' => 25,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('customer.order'), [
        'customer_name' => 'Rafi Ahmed',
        'customer_phone' => '01812345678',
        'order_type' => 'counter',
        'payment_method' => 'cash',
        'notes' => 'Extra sauce please',
        'items' => [
            ['food_id' => $food->id, 'quantity' => 2],
        ],
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', [
        'total_amount' => 340.00,
        'order_status' => 'pending',
    ]);
});

test('cart boy can access cartboy workspace and update live kitchen order status', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $order = Order::create([
        'order_number' => 'FC-STAFF-01',
        'subtotal' => 200.00,
        'total_amount' => 200.00,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
        'order_status' => 'pending',
    ]);

    $response = $this->actingAs($staff)->get(route('cartboy.index'));
    $response->assertOk();
    $response->assertSee('কাউন্টার অর্ডার কার্ট');

    // Cart boy updates order to preparing
    $updateResponse = $this->actingAs($staff)->patchJson(route('cartboy.order-status', $order), [
        'order_status' => 'preparing',
    ]);

    $updateResponse->assertOk();
    $updateResponse->assertJson(['success' => true, 'order_status' => 'preparing']);
    expect($order->fresh()->order_status)->toBe('preparing');
});

test('cart boy is strictly restricted from owner dashboard and redirected to cartboy workspace', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $response = $this->actingAs($staff)->get(route('dashboard'));

    $response->assertRedirect(route('cartboy.index'));
});

test('cart boy can customize food price per user requirement', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $category = Category::create([
        'name' => 'Tea',
        'slug' => 'tea-test',
        'is_active' => true,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Masala Cha',
        'selling_price' => 30.00,
        'cost_price' => 12.00,
        'current_stock' => 50,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($staff)->patchJson(route('foods.update-price', $food), [
        'selling_price' => 50.00,
        'cost_price' => 20.00,
    ]);

    $response->assertOk();
    expect((float) $food->fresh()->selling_price)->toBe(50.00);
});

test('owner can access dashboard, cartboy workspace, and customize food price', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $category = Category::create([
        'name' => 'Pasta',
        'slug' => 'pasta-test',
        'is_active' => true,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Chicken White Pasta',
        'selling_price' => 180.00,
        'cost_price' => 90.00,
        'current_stock' => 20,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    // Owner accesses dashboard
    $this->actingAs($owner)->get(route('dashboard'))->assertOk();

    // Owner accesses cartboy workspace
    $this->actingAs($owner)->get(route('cartboy.index'))->assertOk()->assertSee('ওনার মোড');

    // Owner customizes food price
    $priceResponse = $this->actingAs($owner)->patchJson(route('foods.update-price', $food), [
        'selling_price' => 210.00,
        'cost_price' => 100.00,
    ]);

    $priceResponse->assertOk();
    expect((float) $food->fresh()->selling_price)->toBe(210.00);
});

test('cart boy can poll live orders json for audio alerts and kitchen updates', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $order = Order::create([
        'order_number' => 'FC-POLL-01',
        'subtotal' => 150.00,
        'total_amount' => 150.00,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
        'order_status' => 'pending',
    ]);

    $response = $this->actingAs($staff)->getJson(route('cartboy.live-orders'));

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'live_orders',
        'latest_order_id',
        'pending_count',
        'live_count',
        'completed_today_count',
    ]);
    expect($response->json('pending_count'))->toBeGreaterThanOrEqual(1);
    expect($response->json('latest_order_id'))->toBeGreaterThanOrEqual($order->id);
});

test('customer tracking endpoint returns step indicator and order item breakdown', function () {
    $order = Order::create([
        'order_number' => 'FC-TRACK-99',
        'subtotal' => 100.00,
        'total_amount' => 100.00,
        'payment_method' => 'cash',
        'payment_status' => 'unpaid',
        'order_status' => 'preparing',
    ]);

    $response = $this->getJson(route('customer.track', ['query' => 'FC-TRACK-99']));

    $response->assertOk();
    $response->assertJson([
        'found' => true,
        'order' => [
            'order_number' => 'FC-TRACK-99',
            'status' => 'preparing',
            'step' => 2,
        ],
    ]);
});

test('cartboy workspace renders cleanly with sales timeline and item breakdown on mobile view', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    $category = Category::create(['name' => 'Burgers', 'slug' => 'burgers-test', 'is_active' => true]);
    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Smash Burger',
        'selling_price' => 160.00,
        'cost_price' => 80.00,
        'current_stock' => 10,
        'min_stock' => 2,
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_number' => 'FC-CB-001',
        'order_type' => 'parcel',
        'subtotal' => 160.00,
        'total_amount' => 160.00,
        'payment_method' => 'cash',
        'order_status' => 'completed',
        'created_at' => now(),
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'food_id' => $food->id,
        'food_name' => $food->name,
        'quantity' => 1,
        'unit_price' => 160.00,
        'cost_price' => 80.00,
        'subtotal' => 160.00,
        'profit' => 80.00,
    ]);

    $response = $this->actingAs($staff)->get('/cartboy?view=mobile');
    $response->assertOk();
    $response->assertSee('Smash Burger');
    $response->assertSee('লাইভ সেলস ও আইটেম টাইমলাইন');
    $response->assertSee('ওনার ও কার্ট বয় রেন্টাল সেটেলমেন্ট');
    $response->assertSee("deviceView === 'mobile' ? 'grid grid-cols-2 gap-2'", false);
    $response->assertSee('food.bengali_name || food.name', false);
});
