<?php

use App\Models\User;

test('owner can access financial analytics, expenses, and closing reports', function () {
    $owner = User::factory()->create(['role' => 'owner']);

    $this->actingAs($owner)->get(route('profit-loss.index'))->assertOk();
    $this->actingAs($owner)->get(route('closing.index'))->assertOk();
    $this->actingAs($owner)->get(route('expenses.index'))->assertOk();
    $this->actingAs($owner)->get(route('reports.sales'))->assertOk();
});

test('staff is blocked from financial analytics and redirected to cartboy workspace', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    // Staff cannot view sensitive financial statements
    $plResponse = $this->actingAs($staff)->get(route('profit-loss.index'));
    $plResponse->assertRedirect(route('cartboy.index'));
    $plResponse->assertSessionHas('error');

    // Staff cannot view business expenses
    $expenseResponse = $this->actingAs($staff)->get(route('expenses.index'));
    $expenseResponse->assertRedirect(route('cartboy.index'));

    // Staff cannot view sales reports
    $reportResponse = $this->actingAs($staff)->get(route('reports.sales'));
    $reportResponse->assertRedirect(route('cartboy.index'));
});

test('staff has full access to pos and orders operational routes', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)->get(route('pos.index'))->assertOk();
    $this->actingAs($staff)->get(route('orders.index'))->assertOk();
    $this->actingAs($staff)->get(route('foods.index'))->assertOk();
    $this->actingAs($staff)->get(route('inventory.index'))->assertOk();
    $this->actingAs($staff)->get(route('wastes.index'))->assertOk();
});

test('demo login endpoint instantly logs in owner and staff', function () {
    $ownerResponse = $this->get(route('demo.login', 'owner'));
    $ownerResponse->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    auth()->logout();

    $staffResponse = $this->get(route('demo.login', 'staff'));
    $staffResponse->assertRedirect(route('cartboy.index'));
    $this->assertAuthenticated();
});
