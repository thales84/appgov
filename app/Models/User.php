<?php

namespace App\Models;

use App\Domain\Applications\Models\Application;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Models\CitizenProfile;
use App\Domain\Organizations\Models\AgentAssignment;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'public_id',
        'name',
        'email',
        'account_type',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'account_type' => AccountType::class,
            'status' => AccountStatus::class,
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (empty($user->public_id)) {
                $user->public_id = (string) Str::ulid();
            }
        });
    }

    public function citizenProfile(): HasOne
    {
        return $this->hasOne(CitizenProfile::class);
    }

    public function agentAssignments(): HasMany
    {
        return $this->hasMany(AgentAssignment::class);
    }

    public function activeAssignment(): ?AgentAssignment
    {
        return $this->agentAssignments()->active()->first();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'citizen_id');
    }

    public function isCitizen(): bool
    {
        return $this->account_type === AccountType::Citizen;
    }

    public function isAgent(): bool
    {
        return $this->account_type === AccountType::Agent;
    }

    public function isActive(): bool
    {
        return $this->status === AccountStatus::Active;
    }

    public function hasActiveAgentAssignment(): bool
    {
        return $this->agentAssignments()->active()->exists();
    }

    public function isAssignedToOrganization(int $organizationId): bool
    {
        return $this->agentAssignments()
            ->active()
            ->where('organization_id', $organizationId)
            ->exists();
    }

    /**
     * @return list<int>
     */
    public function activeOrganizationIds(): array
    {
        return $this->agentAssignments()
            ->active()
            ->pluck('organization_id')
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function preferredLocale(): string
    {
        return $this->citizenProfile?->preferred_locale ?? 'fr';
    }
}
