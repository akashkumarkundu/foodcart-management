<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Food;
use App\Models\Order;
use App\Models\Setting;

test('customers can view the daraz style food cart menu with flash sale and vouchers', function () {
    $category = Category::create([
        'name' => 'Fast Food',
        'slug' => 'fast-food',
        'is_active' => true,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Daraz Special Burger',
        'bengali_name' => 'দারাজ স্পেশাল বার্গার',
        'selling_price' => 180.00,
        'cost_price' => 90.00,
        'current_stock' => 20,
        'min_stock' => 2,
        'is_active' => true,
    ]);

    $coupon = Coupon::create([
        'code' => 'DARAZ50',
        'description' => '৳৫০ ছাড়',
        'discount_type' => 'fixed',
        'discount_value' => 50.00,
        'min_order_amount' => 300.00,
        'is_active' => true,
    ]);

    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee('Flash Sale');
    $response->assertSee('দারাজ স্পেশাল বার্গার');
    $response->assertSee('DARAZ50');
    $response->assertSee('খাবার কার্ট ও চেকআউট');
});

test('customers can apply valid coupon code and receive calculated discount', function () {
    Coupon::create([
        'code' => 'DARAZ10',
        'description' => '১০% ছাড়',
        'discount_type' => 'percentage',
        'discount_value' => 10.00,
        'min_order_amount' => 200.00,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('customer.coupon'), [
        'code' => 'DARAZ10',
        'subtotal' => 300.00,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'coupon' => [
                'code' => 'DARAZ10',
                'discount' => 30.00,
            ],
            'new_total' => 270.00,
        ]);
});

test('customers cannot apply coupon if subtotal is below minimum order amount', function () {
    Coupon::create([
        'code' => 'BIGDEAL',
        'description' => '৳১০০ ছাড়',
        'discount_type' => 'fixed',
        'discount_value' => 100.00,
        'min_order_amount' => 500.00,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('customer.coupon'), [
        'code' => 'BIGDEAL',
        'subtotal' => 350.00,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('customers can place order with coupon code and discount is applied', function () {
    $category = Category::create([
        'name' => 'Snacks',
        'slug' => 'snacks',
        'is_active' => true,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Chicken Roll',
        'selling_price' => 150.00,
        'cost_price' => 70.00,
        'current_stock' => 10,
        'min_stock' => 1,
        'is_active' => true,
    ]);

    Coupon::create([
        'code' => 'SAVE50',
        'description' => '৳৫০ ছাড়',
        'discount_type' => 'fixed',
        'discount_value' => 50.00,
        'min_order_amount' => 250.00,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('customer.order'), [
        'customer_name' => 'Rahim Chowdhury',
        'customer_phone' => '01799887766',
        'order_type' => 'parcel',
        'coupon_code' => 'SAVE50',
        'payment_method' => 'cash',
        'items' => [
            ['food_id' => $food->id, 'quantity' => 2], // 2 * 150 = 300
        ],
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'total_amount' => 250.00, // 300 - 50 = 250
            'discount_amount' => 50.00,
        ]);

    $this->assertDatabaseHas('orders', [
        'order_type' => 'parcel',
        'discount_amount' => 50.00,
        'total_amount' => 250.00,
    ]);

    $this->assertDatabaseHas('customers', [
        'phone' => '01799887766',
        'name' => 'Rahim Chowdhury',
    ]);
});

test('customers can place dine-in order with table number and it is recorded', function () {
    $category = Category::create([
        'name' => 'Meals',
        'slug' => 'meals',
        'is_active' => true,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Special Noodles',
        'bengali_name' => 'স্পেশাল নুডুলস',
        'selling_price' => 120.00,
        'cost_price' => 60.00,
        'current_stock' => 15,
        'min_stock' => 1,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('customer.order'), [
        'customer_name' => 'Sakib Al Hasan',
        'customer_phone' => '01711223344',
        'order_type' => 'dine_in',
        'table_no' => 'টেবিল ৩',
        'payment_method' => 'cash',
        'items' => [
            ['food_id' => $food->id, 'quantity' => 1],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', [
        'order_type' => 'dine_in',
    ]);

    $order = Order::latest('id')->first();
    expect($order->notes)->toContain('টেবিল ৩');
});

test('customers can place digital payment order via bKash with transaction id', function () {
    $category = Category::create([
        'name' => 'Beverage',
        'slug' => 'beverage',
        'is_active' => true,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Cold Coffee',
        'bengali_name' => 'কোল্ড কফি',
        'selling_price' => 80.00,
        'cost_price' => 35.00,
        'current_stock' => 20,
        'min_stock' => 2,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('customer.order'), [
        'customer_name' => 'Tamim Iqbal',
        'customer_phone' => '01811223344',
        'order_type' => 'dine_in',
        'table_no' => 'টেবিল ১',
        'payment_method' => 'bkash',
        'transaction_id' => '9J7A2B6C8D',
        'items' => [
            ['food_id' => $food->id, 'quantity' => 1],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', [
        'payment_method' => 'bkash',
        'payment_status' => 'paid',
    ]);
});

test('digital menu page shows rajshahi mohila college address, theme toggle and staff login modal', function () {
    Setting::set('cart_address', 'রাজশাহী সরকারি মহিলা কলেজ গেট সংলগ্ন, রাজশাহী');

    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee('রাজশাহী সরকারি মহিলা কলেজ');
    $response->assertSee('স্টাফ ও ওনার লগইন প্যানেল');
    $response->assertSee('toggleTheme()');
});
