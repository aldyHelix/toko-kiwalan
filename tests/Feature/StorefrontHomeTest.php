<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia;

it('renders the storefront home page', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Home')
            ->where('appName', config('app.name')));
});
