<?php

use App\Models\User;

test('users can authenticate using their phone number', function () {
    $user = User::factory()->create([
        'phone' => '01711999888',
        'password' => bcrypt('secret123'),
    ]);

    $response = $this->post(route('login.store'), [
        'email' => '01711999888',
        'password' => 'secret123',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($user);
});

test('users can still authenticate using their email', function () {
    $user = User::factory()->create([
        'email' => 'operator@foodcart.test',
        'phone' => '01811777666',
        'password' => bcrypt('secret123'),
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'operator@foodcart.test',
        'password' => 'secret123',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($user);
});

test('users cannot authenticate with wrong password using phone', function () {
    $user = User::factory()->create([
        'phone' => '01711222333',
        'password' => bcrypt('secret123'),
    ]);

    $response = $this->post(route('login.store'), [
        'email' => '01711222333',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest();
});
