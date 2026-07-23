<?php

use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Enums\IdentityLevel;
use App\Models\User;
use App\Notifications\PasswordChanged;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

it('registers only a citizen with a profile and sends email verification', function () {
    Notification::fake();

    $response = $this->post('/register', [
        'first_name' => 'Amina',
        'last_name' => 'Njoya',
        'email' => 'amina@example.cm',
        'password' => 'SecurePassword123',
        'password_confirmation' => 'SecurePassword123',
        'terms' => true,
        'locale' => 'fr',
        'account_type' => 'agent',
    ]);

    $response->assertRedirect(route('verification.notice'));
    $this->assertAuthenticated();

    $user = User::where('email', 'amina@example.cm')->firstOrFail();

    expect($user->account_type)->toBe(AccountType::Citizen)
        ->and($user->public_id)->toHaveLength(26)
        ->and($user->citizenProfile->first_name)->toBe('Amina')
        ->and($user->citizenProfile->identity_level)->toBe(IdentityLevel::Unverified);

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('requires email verification before opening the citizen dashboard', function () {
    $citizen = User::factory()->unverified()->create();

    $this->actingAs($citizen)
        ->get(route('account.dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('updates the identity level when the email address is verified', function () {
    $citizen = User::factory()->unverified()->create();
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $citizen->getKey(), 'hash' => sha1($citizen->getEmailForVerification())]
    );

    $this->actingAs($citizen)->get($verificationUrl)->assertRedirect();

    expect($citizen->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and($citizen->citizenProfile->fresh()->identity_level)->toBe(IdentityLevel::EmailVerified);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'email.verified',
        'subject_id' => $citizen->id,
    ]);
});

it('keeps citizen and agent login portals separate', function () {
    $password = 'SecurePassword123';
    $citizen = User::factory()->create(['password' => Hash::make($password)]);
    $agent = User::factory()->agent()->create(['password' => Hash::make($password)]);

    $this->post(route('agent.login.store'), [
        'email' => $citizen->email,
        'password' => $password,
        'portal' => 'agent',
    ])->assertSessionHasErrors('email');
    $this->assertGuest();

    $this->post(route('login.store'), [
        'email' => $agent->email,
        'password' => $password,
        'portal' => 'citizen',
    ])->assertSessionHasErrors('email');
    $this->assertGuest();

    $this->post(route('login.store'), [
        'email' => $citizen->email,
        'password' => $password,
        'portal' => 'citizen',
    ])->assertRedirect(route('account.dashboard'));
    $this->assertAuthenticatedAs($citizen);
});

it('does not authenticate a suspended account', function () {
    $password = 'SecurePassword123';
    $citizen = User::factory()->suspended()->create(['password' => Hash::make($password)]);

    $this->post(route('login.store'), [
        'email' => $citizen->email,
        'password' => $password,
        'portal' => 'citizen',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('sends and consumes a one-time password reset token', function () {
    Notification::fake();
    $user = User::factory()->create();
    $token = null;

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHas('status');

    Notification::assertSentTo(
        $user,
        ResetPassword::class,
        function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        }
    );

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'AnotherSecure123',
        'password_confirmation' => 'AnotherSecure123',
    ])->assertRedirect(route('login'));

    expect(Hash::check('AnotherSecure123', $user->fresh()->password))->toBeTrue();
    Notification::assertSentTo($user, PasswordChanged::class);
    $this->assertDatabaseHas('activity_log', [
        'event' => 'password.reset',
        'subject_id' => $user->id,
    ]);
});
