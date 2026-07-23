<?php

namespace App\Actions\Fortify;

use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Enums\IdentityLevel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $input['email'] = Str::lower(trim($input['email'] ?? ''));

        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'terms' => ['accepted'],
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => trim($input['first_name'].' '.$input['last_name']),
                'email' => $input['email'],
                'account_type' => AccountType::Citizen,
                'status' => AccountStatus::Active,
                'password' => Hash::make($input['password']),
            ]);

            $user->citizenProfile()->create([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'preferred_locale' => in_array($input['locale'] ?? null, ['fr', 'en'], true)
                    ? $input['locale']
                    : 'fr',
                'identity_level' => IdentityLevel::Unverified,
            ]);

            return $user;
        });
    }
}
