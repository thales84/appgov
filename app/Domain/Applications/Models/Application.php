<?php

namespace App\Domain\Applications\Models;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Appointments\Models\Appointment;
use App\Domain\Archiving\Models\ArchivePackage;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Domain\Decisions\Models\Decision;
use App\Domain\Deliveries\Models\Delivery;
use App\Domain\Examinations\Models\ExamAttempt;
use App\Domain\Organizations\Models\AdministrativeUnit;
use App\Domain\Payments\Models\Invoice;
use App\Domain\Production\Models\IssuedDocument;
use App\Domain\Production\Models\ProductionOrder;
use App\Domain\Shared\Concerns\HasPublicId;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;

class Application extends Model
{
    use HasPublicId;

    protected $fillable = [
        'citizen_id',
        'assigned_unit_id',
        'assigned_agent_id',
        'procedure_version_id',
        'status',
        'reference',
        'draft_key',
        'form_responses',
        'snapshot',
        'started_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'form_responses' => 'array',
            'snapshot' => 'array',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (): never {
            throw new LogicException('Applications are never physically deleted by normal operations.');
        });
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function assignedUnit(): BelongsTo
    {
        return $this->belongsTo(AdministrativeUnit::class, 'assigned_unit_id');
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function procedureVersion(): BelongsTo
    {
        return $this->belongsTo(ProcedureVersion::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ApplicationParticipant::class);
    }

    public function submittedDocuments(): HasMany
    {
        return $this->hasMany(SubmittedDocument::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ApplicationEvent::class)->orderBy('created_at', 'desc');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ApplicationAssignment::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ApplicationMessage::class)->orderBy('created_at', 'asc');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class)->orderBy('decided_at', 'desc');
    }

    public function latestDecision(): HasOne
    {
        return $this->hasOne(Decision::class)->latestOfMany('decided_at');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class)->latestOfMany();
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class)->orderBy('attempt_number', 'asc');
    }

    public function productionOrder(): HasOne
    {
        return $this->hasOne(ProductionOrder::class);
    }

    public function issuedDocument(): HasOne
    {
        return $this->hasOne(IssuedDocument::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function archivePackage(): HasOne
    {
        return $this->hasOne(ArchivePackage::class);
    }
}
