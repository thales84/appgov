<?php

namespace Database\Factories;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Enums\IdentityLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'account_type' => AccountType::Citizen,
            'status' => AccountStatus::Active,
            'password' => Hash::make(Str::password(32)),
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if (! $user->isCitizen() || $user->citizenProfile()->exists()) {
                return;
            }

            [$firstName, $lastName] = array_pad(explode(' ', $user->name, 2), 2, 'Citizen');

            $user->citizenProfile()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'preferred_locale' => 'fr',
                'identity_level' => $user->hasVerifiedEmail()
                    ? IdentityLevel::EmailVerified
                    : IdentityLevel::Unverified,
            ]);
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function agent(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => AccountType::Agent,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AccountStatus::Suspended,
        ]);
    }
}
