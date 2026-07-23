<?php

namespace App\Domain\Catalog\Enums;

enum FormFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Select = 'select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case Date = 'date';
    case Number = 'number';
    case Email = 'email';
    case Telephone = 'tel';
}
