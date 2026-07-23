<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the public portal with Inertia', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home')
            ->where('locale', 'fr')
            ->where('app.displayTimezone', 'Europe/Paris')
        );
});

it('exposes the application health endpoint', function () {
    $this->get('/health')->assertOk();
});
