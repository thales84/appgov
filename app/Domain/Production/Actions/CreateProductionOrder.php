<?php

namespace App\Domain\Production\Actions;

use App\Domain\Applications\Enums\ApplicationStatus;
use App\Domain\Applications\Models\Application;
use App\Domain\Applications\Services\TransitionApplicationWorkflow;
use App\Domain\Production\Enums\ProductionOrderStatus;
use App\Domain\Production\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateProductionOrder
{
    public function __construct(
        private readonly TransitionApplicationWorkflow $workflow
    ) {}

    public function execute(Application $application, User $agent): ProductionOrder
    {
        return DB::transaction(function () use ($application, $agent) {
            $order = ProductionOrder::create([
                'application_id' => $application->id,
                'status' => ProductionOrderStatus::InProduction,
            ]);

            $this->workflow->execute(
                $application,
                ApplicationStatus::InProduction,
                $agent,
                'Lancement de la confection du titre officiel.',
                'Official document production order queued.'
            );

            activity('production')
                ->causedBy($agent)
                ->performedOn($order)
                ->event('production.order_created')
                ->withProperties([
                    'application_public_id' => $application->public_id,
                ])
                ->log('Production order created.');

            return $order;
        });
    }
}
