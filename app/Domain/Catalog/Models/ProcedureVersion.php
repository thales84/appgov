<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Shared\Concerns\HasPublicId;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class ProcedureVersion extends Model
{
    use HasPublicId;

    private bool $allowsLifecycleMutation = false;

    protected $fillable = [
        'service_id',
        'version_number',
        'title_fr',
        'title_en',
        'summary_fr',
        'summary_en',
        'description_fr',
        'description_en',
        'eligibility_fr',
        'eligibility_en',
        'legal_basis_fr',
        'legal_basis_en',
        'is_demo',
        'effective_from',
        'effective_until',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'status' => ProcedureVersionStatus::class,
            'is_demo' => 'boolean',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
            'review_submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
            'retired_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (ProcedureVersion $version): void {
            $originalStatus = ProcedureVersionStatus::from($version->getRawOriginal('status'));

            if ($version->isDirty('status') && ! $version->allowsLifecycleMutation) {
                throw new LogicException('Procedure status changes must use the lifecycle service.');
            }

            if (
                $originalStatus !== ProcedureVersionStatus::Draft
                && ! $version->allowsLifecycleMutation
            ) {
                throw new LogicException('A procedure version under review, published, or retired is immutable.');
            }

            if ($version->allowsLifecycleMutation) {
                $allowed = [
                    'status',
                    'review_submitted_at',
                    'review_submitted_by_user_id',
                    'reviewed_at',
                    'reviewed_by_user_id',
                    'review_note',
                    'published_at',
                    'published_by_user_id',
                    'retired_at',
                    'retired_by_user_id',
                    'updated_at',
                ];

                if (array_diff(array_keys($version->getDirty()), $allowed) !== []) {
                    throw new LogicException('A lifecycle transition may not change procedure content.');
                }
            }
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProcedureStep::class)->orderBy('position');
    }

    public function procedureSteps(): HasMany
    {
        return $this->steps();
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('position');
    }

    public function documentRequirements(): HasMany
    {
        return $this->hasMany(DocumentRequirement::class)->orderBy('position');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ProcedureRule::class)->orderBy('position');
    }

    public function feeSchedules(): HasMany
    {
        return $this->hasMany(FeeSchedule::class)->orderBy('position');
    }

    public function isDraft(): bool
    {
        return $this->status === ProcedureVersionStatus::Draft;
    }

    public function submitForReview(User $actor): void
    {
        $this->performLifecycleMutation(function () use ($actor): void {
            $this->forceFill([
                'status' => ProcedureVersionStatus::UnderReview,
                'review_submitted_at' => now(),
                'review_submitted_by_user_id' => $actor->id,
                'reviewed_at' => null,
                'reviewed_by_user_id' => null,
                'review_note' => null,
            ])->save();
        });
    }

    public function returnToDraft(User $actor, string $reason): void
    {
        $this->performLifecycleMutation(function () use ($actor, $reason): void {
            $this->forceFill([
                'status' => ProcedureVersionStatus::Draft,
                'reviewed_at' => now(),
                'reviewed_by_user_id' => $actor->id,
                'review_note' => $reason,
            ])->save();
        });
    }

    public function publish(User $actor): void
    {
        $this->performLifecycleMutation(function () use ($actor): void {
            $this->forceFill([
                'status' => ProcedureVersionStatus::Published,
                'reviewed_at' => now(),
                'reviewed_by_user_id' => $actor->id,
                'published_at' => now(),
                'published_by_user_id' => $actor->id,
            ])->save();
        });
    }

    public function retire(User $actor): void
    {
        $this->performLifecycleMutation(function () use ($actor): void {
            $this->forceFill([
                'status' => ProcedureVersionStatus::Retired,
                'retired_at' => now(),
                'retired_by_user_id' => $actor->id,
            ])->save();
        });
    }

    private function performLifecycleMutation(callable $transition): void
    {
        $this->allowsLifecycleMutation = true;

        try {
            $transition();
        } finally {
            $this->allowsLifecycleMutation = false;
        }
    }
}
