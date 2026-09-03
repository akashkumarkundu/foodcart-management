<?php

use App\Models\Category;
use App\Models\Food;
use App\Models\Review;

test('customers can submit a review for a food item', function () {
    $category = Category::create([
        'name' => 'Fast Food',
        'slug' => 'fast-food',
        'is_active' => true,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => 'Beef Naga Burger',
        'selling_price' => 190.00,
        'cost_price' => 105.00,
        'current_stock' => 50,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    $response = $this->post(route('customer.review'), [
        'customer_name' => 'Sakib Al Hasan',
        'customer_phone' => '01711223344',
        'food_id' => $food->id,
        'rating' => 5,
        'comment' => 'অসাধারণ নাগা বার্গার! স্পাইসি এবং ক্রিস্পি ছিল।',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('reviews', [
        'customer_name' => 'Sakib Al Hasan',
        'food_id' => $food->id,
        'rating' => 5,
        'is_approved' => true,
    ]);
});

test('public homepage displays customer reviews', function () {
    $review = Review::create([
        'customer_name' => 'Tamim Iqbal',
        'customer_phone' => '01811556677',
        'rating' => 5,
        'comment' => 'পদ্মা গার্ডেনের সেরা বিফ তেহারি!',
        'is_approved' => true,
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Tamim Iqbal');
    $response->assertSee('পদ্মা গার্ডেনের সেরা বিফ তেহারি!');
});

test('review submission validates required fields', function () {
    $response = $this->post(route('customer.review'), [
        'customer_name' => '',
        'rating' => 6, // invalid
        'comment' => '',
    ]);

    $response->assertSessionHasErrors(['customer_name', 'rating', 'comment']);
});
