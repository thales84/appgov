<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Enums\ProcedureVersionStatus;
use App\Domain\Catalog\Models\ProcedureVersion;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use LogicException;

class UpdateProcedureVersion
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(ProcedureVersion $version, User $actor, array $data): ProcedureVersion
    {
        return DB::transaction(function () use ($actor, $data, $version): ProcedureVersion {
            $lockedVersion = ProcedureVersion::query()
                ->whereKey($version->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedVersion->status !== ProcedureVersionStatus::Draft) {
                throw new LogicException('Only a draft procedure version may be edited.');
            }

            $lockedVersion->update([
                'title_fr' => $data['title_fr'],
                'title_en' => $data['title_en'],
                'summary_fr' => $data['summary_fr'],
                'summary_en' => $data['summary_en'],
                'description_fr' => $data['description_fr'] ?? null,
                'description_en' => $data['description_en'] ?? null,
                'eligibility_fr' => $data['eligibility_fr'] ?? null,
                'eligibility_en' => $data['eligibility_en'] ?? null,
                'legal_basis_fr' => $data['legal_basis_fr'] ?? null,
                'legal_basis_en' => $data['legal_basis_en'] ?? null,
                'effective_from' => $data['effective_from']
                    ? $this->toUtcStartOfDay($data['effective_from'])
                    : null,
                'is_demo' => $data['is_demo'],
            ]);

            foreach ([
                $lockedVersion->steps(),
                $lockedVersion->formFields(),
                $lockedVersion->documentRequirements(),
                $lockedVersion->rules(),
                $lockedVersion->feeSchedules(),
            ] as $relation) {
                $relation->increment('position', 10000);
            }

            [$stepIds, $keptStepIds] = $this->syncSteps($lockedVersion, $data['steps']);

            $this->syncItems(
                $lockedVersion->formFields(),
                $data['fields'],
                fn (array $item, int $position) => [
                    ...$item,
                    'procedure_step_id' => $stepIds[$item['step_code']],
                    'position' => $position,
                    'configuration' => [
                        'options' => [
                            'fr' => $this->parseOptions($item['options_fr'] ?? null),
                            'en' => $this->parseOptions($item['options_en'] ?? null),
                        ],
                    ],
                ],
            );
            $this->syncItems(
                $lockedVersion->documentRequirements(),
                $data['documents'],
                fn (array $item, int $position) => [
                    ...$item,
                    'procedure_step_id' => $item['step_code'] ? $stepIds[$item['step_code']] : null,
                    'position' => $position,
                    'condition_rules' => null,
                    'allowed_mime_types' => null,
                    'max_file_size_kb' => null,
                ],
            );
            $this->syncItems(
                $lockedVersion->rules(),
                $data['rules'],
                fn (array $item, int $position) => [
                    ...$item,
                    'position' => $position,
                    'configuration' => null,
                ],
            );
            $this->syncItems(
                $lockedVersion->feeSchedules(),
                $data['fees'],
                fn (array $item, int $position) => [
                    ...$item,
                    'procedure_step_id' => $item['step_code'] ? $stepIds[$item['step_code']] : null,
                    'position' => $position,
                ],
            );

            $lockedVersion->steps()->whereNotIn('id', $keptStepIds)->get()->each->delete();

            activity('catalog')
                ->causedBy($actor)
                ->performedOn($lockedVersion)
                ->event('catalog.procedure.updated')
                ->withProperties([
                    'version_number' => $lockedVersion->version_number,
                    'counts' => [
                        'steps' => count($data['steps']),
                        'fields' => count($data['fields']),
                        'documents' => count($data['documents']),
                        'rules' => count($data['rules']),
                        'fees' => count($data['fees']),
                    ],
                ])
                ->log('Draft procedure definition updated.');

            return $lockedVersion->fresh();
        });
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{array<string, int>, list<int>}
     */
    private function syncSteps(ProcedureVersion $version, array $items): array
    {
        $stepIds = [];
        $keptIds = [];

        foreach (array_values($items) as $index => $item) {
            $step = $this->resolveExisting($version->steps(), $item['public_id'] ?? null);
            $attributes = [
                'code' => $item['code'],
                'position' => $index + 1,
                'name_fr' => $item['name_fr'],
                'name_en' => $item['name_en'],
                'description_fr' => $item['description_fr'] ?? null,
                'description_en' => $item['description_en'] ?? null,
                'step_type' => $item['step_type'],
                'is_required' => $item['is_required'],
                'transition_rules' => null,
            ];

            if ($step) {
                $step->update($attributes);
            } else {
                $step = $version->steps()->create($attributes);
            }

            $stepIds[$step->code] = $step->id;
            $keptIds[] = $step->id;
        }

        return [$stepIds, $keptIds];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function syncItems(HasMany $relation, array $items, callable $attributesFor): void
    {
        $keptIds = [];

        foreach (array_values($items) as $index => $item) {
            $model = $this->resolveExisting($relation, $item['public_id'] ?? null);
            $attributes = $attributesFor($item, $index + 1);
            unset($attributes['public_id'], $attributes['step_code']);

            if ($model) {
                $model->update($attributes);
            } else {
                $model = $relation->create($attributes);
            }

            $keptIds[] = $model->id;
        }

        $relation->whereNotIn('id', $keptIds)->get()->each->delete();
    }

    private function resolveExisting(HasMany $relation, ?string $publicId): mixed
    {
        return $publicId
            ? $relation->where('public_id', $publicId)->firstOrFail()
            : null;
    }

    private function toUtcStartOfDay(string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date, config('appgov.display_timezone'))
            ->startOfDay()
            ->utc();
    }

    /**
     * @return list<string>
     */
    private function parseOptions(?string $options): array
    {
        return collect(preg_split('/\R/u', $options ?? '') ?: [])
            ->map(fn (string $option) => trim($option))
            ->filter()
            ->take(50)
            ->values()
            ->all();
    }
}
