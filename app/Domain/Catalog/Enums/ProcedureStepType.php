<?php

namespace App\Domain\Catalog\Enums;

enum ProcedureStepType: string
{
    case Form = 'form';
    case Review = 'review';
    case Payment = 'payment';
    case Appointment = 'appointment';
    case Examination = 'examination';
    case Decision = 'decision';
    case Production = 'production';
    case Delivery = 'delivery';
}
