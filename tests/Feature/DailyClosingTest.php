<?php

use App\Models\DailyReport;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Services\DailyClosingService;
use Carbon\Carbon;

test('daily closing calculates net profit as sales minus expenses minus waste', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $today = Carbon::today();

    // 1. Order of ৳1,000 paid via cash
    $order = Order::create([
        'order_number' => 'FC-20260901-0001',
        'subtotal' => 1000.00,
        'total_amount' => 1000.00,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'order_status' => 'completed',
        'user_id' => $owner->id,
        'created_at' => $today->copy()->setTime(14, 0),
    ]);

    Payment::create([
        'order_id' => $order->id,
        'payment_method' => 'cash',
        'amount' => 1000.00,
        'status' => 'completed',
        'payment_date' => $today->copy()->setTime(14, 0),
    ]);

    // 2. Expense of ৳300
    $expCat = ExpenseCategory::create(['name' => 'Gas', 'slug' => 'gas']);
    Expense::create([
        'expense_category_id' => $expCat->id,
        'user_id' => $owner->id,
        'description' => 'Gas Cylinder Refill',
        'amount' => 300.00,
        'payment_method' => 'cash',
        'date' => $today,
    ]);

    // Close day
    $closingService = app(DailyClosingService::class);
    $report = $closingService->closeDay($today, $owner, 'End of day audit', 1000.00);

    // Sales: 1000, Expense: 300, Waste: 0 => Net Profit = 700
    expect((float) $report->total_sales)->toBe(1000.00);
    expect((float) $report->total_expenses)->toBe(300.00);
    expect((float) $report->net_profit)->toBe(700.00);
    expect($report->is_closed)->toBeTrue();
});

test('owner can reopen closed business day', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $today = Carbon::today();

    $report = DailyReport::create([
        'report_date' => $today->toDateString(),
        'total_orders' => 5,
        'total_sales' => 2500.00,
        'is_closed' => true,
        'closed_by' => $owner->id,
    ]);

    $response = $this->actingAs($owner)->post(route('closing.reopen', $report));

    $response->assertRedirect();
    expect($report->fresh()->is_closed)->toBeFalse();
});

test('staff or owner can toggle cart open and closed status', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    Setting::set('is_cart_open', true);

    $response = $this->actingAs($staff)->post(route('cart.toggle-status'));

    $response->assertRedirect();
    expect(Setting::get('is_cart_open'))->toBeFalse();

    // Toggle back
    $response2 = $this->actingAs($staff)->post(route('cart.toggle-status'));
    $response2->assertRedirect();
    expect(Setting::get('is_cart_open'))->toBeTrue();
});

test('daily closing preview includes sales timeline, item sales, and rental model calculations', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $today = Carbon::today();

    Setting::set('daily_cart_rent', 500.0);

    $closingService = app(DailyClosingService::class);
    $preview = $closingService->getClosingPreview($today);

    expect($preview)->toHaveKeys(['sales_timeline', 'item_wise_sales', 'cart_rent', 'cart_boy_net', 'parcel_orders', 'dine_in_orders']);
    expect($preview['cart_rent'])->toBe(500.0);
});
