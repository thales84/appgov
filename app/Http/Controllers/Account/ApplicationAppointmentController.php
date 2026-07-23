<?php

namespace App\Http\Controllers\Account;

use App\Domain\Applications\Models\Application;
use App\Domain\Appointments\Actions\BookAppointment;
use App\Domain\Appointments\Models\AppointmentSlot;
use App\Domain\Appointments\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationAppointmentController extends Controller
{
    public function show(Application $application): Response
    {
        Gate::authorize('view', $application);

        $application->load(['appointment.slot.location', 'procedureVersion.service']);

        $locations = Location::query()
            ->with(['slots' => function ($q) {
                $q->where('is_active', true)
                    ->where('starts_at', '>=', now())
                    ->orderBy('starts_at', 'asc');
            }])
            ->where('is_active', true)
            ->where('organization_id', $application->procedureVersion->service->organization_id)
            ->get();

        return Inertia::render('Account/Applications/AppointmentBooking', [
            'application' => [
                'publicId' => $application->public_id,
                'reference' => $application->reference,
                'procedureTitle' => ['fr' => $application->procedureVersion->title_fr, 'en' => $application->procedureVersion->title_en],
                'appointment' => $application->appointment ? [
                    'publicId' => $application->appointment->public_id,
                    'status' => $application->appointment->status->value,
                    'statusLabel' => $application->appointment->status->label(app()->getLocale()),
                    'scheduledAt' => $application->appointment->scheduled_at->toIso8601String(),
                    'locationName' => ['fr' => $application->appointment->slot->location->name_fr, 'en' => $application->appointment->slot->location->name_en],
                    'address' => $application->appointment->slot->location->address,
                ] : null,
            ],
            'locations' => $locations->map(fn ($loc) => [
                'publicId' => $loc->public_id,
                'name' => ['fr' => $loc->name_fr, 'en' => $loc->name_en],
                'address' => $loc->address,
                'slots' => $loc->slots->map(fn ($slot) => [
                    'publicId' => $slot->public_id,
                    'startsAt' => $slot->starts_at->toIso8601String(),
                    'endsAt' => $slot->ends_at->toIso8601String(),
                    'maxCapacity' => $slot->max_capacity,
                    'bookedCount' => $slot->booked_count,
                    'isFull' => $slot->isFull(),
                ]),
            ]),
        ]);
    }

    public function store(
        Request $request,
        Application $application,
        BookAppointment $action,
    ): RedirectResponse {
        Gate::authorize('view', $application);

        $request->validate([
            'slot_public_id' => ['required', 'string'],
        ]);

        $slot = AppointmentSlot::query()
            ->where('public_id', $request->input('slot_public_id'))
            ->firstOrFail();

        $action->execute($application, $slot);

        return back()->with('status', 'appointment.scheduled');
    }
}
