<?php

namespace App\Domain\Applications\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Catalog\Models\Service;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StartApplication
{
    public function execute(User $citizen, Service $service): Application
    {
        $draftKey = null;

        try {
            return DB::transaction(function () use ($citizen, &$draftKey, $service): Application {
                $lockedService = Service::query()
                    ->with(['currentProcedureVersion', 'organization'])
                    ->whereKey($service->id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                $version = $lockedService->currentProcedureVersion;

                if (! $version || ! $lockedService->organization->is_active) {
                    throw new NotFoundHttpException;
                }

                $draftKey = hash('sha256', "$citizen->id:$version->id");

                $existing = Application::query()
                    ->where('draft_key', $draftKey)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return $existing;
                }

                $application = Application::create([
                    'citizen_id' => $citizen->id,
                    'procedure_version_id' => $version->id,
                    'status' => ApplicationStatus::Draft,
                    'draft_key' => $draftKey,
                    'started_at' => now(),
                ]);

                activity('applications')
                    ->causedBy($citizen)
                    ->performedOn($application)
                    ->event('application.started')
                    ->withProperties([
                        'procedure_version_public_id' => $version->public_id,
                        'service_public_id' => $lockedService->public_id,
                    ])
                    ->log('Citizen started an application draft.');

                return $application;
            }, 3);
        } catch (QueryException $exception) {
            $application = $draftKey
                ? Application::query()->where('draft_key', $draftKey)->first()
                : null;

            if ($application) {
                return $application;
            }

            throw $exception;
        }
    }
}
