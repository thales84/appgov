<?php

namespace App\Domain\Appointments\Actions;

use App\Domain\Applications\Models\Application;
use App\Domain\Appointments\Enums\AppointmentStatus;
use App\Domain\Appointments\Models\Appointment;
use App\Domain\Appointments\Models\AppointmentSlot;
use Illuminate\Support\Facades\DB;
use LogicException;

class BookAppointment
{
    public function execute(Application $application, AppointmentSlot $slot): Appointment
    {
        return DB::transaction(function () use ($application, $slot) {
            $lockedSlot = AppointmentSlot::query()
                ->whereKey($slot->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSlot->isFull()) {
                throw new LogicException('This appointment slot is fully booked.');
            }

            // Cancel any existing active appointment for this application
            Appointment::query()
                ->where('application_id', $application->id)
                ->where('status', AppointmentStatus::Scheduled)
                ->update(['status' => AppointmentStatus::Cancelled]);

            $appointment = Appointment::create([
                'application_id' => $application->id,
                'appointment_slot_id' => $lockedSlot->id,
                'citizen_id' => $application->citizen_id,
                'status' => AppointmentStatus::Scheduled,
                'scheduled_at' => $lockedSlot->starts_at,
            ]);

            $lockedSlot->increment('booked_count');

            activity('appointments')
                ->causedBy($application->citizen)
                ->performedOn($appointment)
                ->event('appointment.scheduled')
                ->withProperties([
                    'application_public_id' => $application->public_id,
                    'slot_public_id' => $lockedSlot->public_id,
                    'scheduled_at' => $lockedSlot->starts_at->toIso8601String(),
                ])
                ->log('Appointment scheduled by citizen.');

            return $appointment;
        });
    }
}
