<?php

declare(strict_types=1);

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('renders the register page', function () {
    $this->get('/register')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth/Register'));
});

it('registers a new customer and logs them in on the web guard', function () {
    $response = $this->post('/register', [
        'name' => 'Sari Dewi',
        'email' => 'sari@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticatedAs(User::firstWhere('email', 'sari@example.com'), 'web');
});

it('rejects registration with a mismatched password confirmation', function () {
    $this->from('/register')->post('/register', [
        'name' => 'Sari Dewi',
        'email' => 'sari@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');

    $this->assertGuest('web');
});

it('renders the login page', function () {
    $this->get('/login')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth/Login'));
});

it('logs in a customer with valid credentials', function () {
    $user = User::factory()->create(['password' => Hash::make('password123')]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user, 'web');
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create(['password' => Hash::make('password123')]);

    $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('web');
});

it('throttles login after too many failed attempts', function () {
    $user = User::factory()->create(['password' => Hash::make('password123')]);

    // Exhaust the 5-attempt allowance.
    foreach (range(1, 5) as $ignored) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
    }

    // The next attempt is locked out (throttled) rather than a credential check,
    // and a correct password is still rejected while the lock holds.
    $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest('web');
});

it('logs out an authenticated customer', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->post('/logout')
        ->assertRedirect(route('home'));

    $this->assertGuest('web');
});

it('keeps the customer and admin guards separate', function () {
    $user = User::factory()->create();

    // A customer authenticated on web is not authenticated as an admin...
    $this->actingAs($user, 'web');
    expect(auth('admin')->check())->toBeFalse();

    // ...and cannot reach the admin panel.
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('redirects authenticated customers away from guest-only auth pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')->get('/login')->assertRedirect();
});
