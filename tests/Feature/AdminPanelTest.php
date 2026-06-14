<?php

declare(strict_types=1);

use App\Models\Admin;

it('redirects guests to the admin login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('renders the admin login page', function () {
    $this->get('/admin/login')->assertOk();
});

it('authenticates an admin into the panel', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->get('/admin')
        ->assertOk();
});
