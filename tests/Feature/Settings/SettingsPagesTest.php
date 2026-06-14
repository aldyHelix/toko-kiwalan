<?php

declare(strict_types=1);

use App\Models\Admin;

beforeEach(function () {
    $this->actingAs(Admin::factory()->admin()->create(), 'admin');
});

it('renders the general settings page with the current value', function () {
    $this->get('/admin/global-settings')
        ->assertOk()
        ->assertSee('Pengaturan Umum')
        ->assertSee('Toko Kiwalan');
});

it('renders the seo settings page', function () {
    $this->get('/admin/seo-settings')
        ->assertOk()
        ->assertSee('Pengaturan SEO');
});

it('renders the payment settings page', function () {
    $this->get('/admin/payment-settings')
        ->assertOk()
        ->assertSee('Pengaturan Pembayaran');
});

it('guards settings pages behind the admin guard', function () {
    auth('admin')->logout();

    $this->get('/admin/global-settings')->assertRedirect('/admin/login');
});
