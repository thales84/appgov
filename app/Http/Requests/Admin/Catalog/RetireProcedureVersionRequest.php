<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class RetireProcedureVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('retire', $this->route('version')) ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
