<?php

use App\Domain\Identity\Enums\IdentityLevel;
use App\Models\User;
use App\Notifications\EmailAddressChanged;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

it('lets a citizen declare a complete profile and records an audit', function () {
    $citizen = User::factory()->create();

    $this->actingAs($citizen)
        ->put(route('account.profile.update'), [
            'first_name' => 'Amina',
            'last_name' => 'Njoya',
            'email' => $citizen->email,
            'phone' => '+237 699 00 00 00',
            'preferred_locale' => 'en',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'citizen-profile-updated');

    expect($citizen->fresh()->name)->toBe('Amina Njoya')
        ->and($citizen->citizenProfile->fresh()->preferred_locale)->toBe('en')
        ->and($citizen->citizenProfile->fresh()->identity_level)->toBe(IdentityLevel::IdentityDeclared);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'citizen_profile.updated',
        'subject_id' => $citizen->id,
    ]);
});

it('requires email verification again after an email address change', function () {
    Notification::fake();
    $citizen = User::factory()->create();

    $this->actingAs($citizen)
        ->put(route('account.profile.update'), [
            'first_name' => $citizen->citizenProfile->first_name,
            'last_name' => $citizen->citizenProfile->last_name,
            'email' => 'new-address@example.cm',
            'phone' => '+237 699 00 00 00',
            'preferred_locale' => 'fr',
        ])
        ->assertRedirect();

    expect($citizen->fresh()->hasVerifiedEmail())->toBeFalse()
        ->and($citizen->citizenProfile->fresh()->identity_level)->toBe(IdentityLevel::Unverified);

    Notification::assertSentTo($citizen, VerifyEmail::class);
    Notification::assertSentOnDemand(EmailAddressChanged::class);

    $this->get(route('account.dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('forbids an agent from updating a citizen profile', function () {
    $agent = User::factory()->agent()->create();

    $this->actingAs($agent)
        ->put(route('account.profile.update'), [
            'first_name' => 'Agent',
            'last_name' => 'Test',
            'email' => $agent->email,
            'phone' => null,
            'preferred_locale' => 'fr',
        ])
        ->assertForbidden();
});
