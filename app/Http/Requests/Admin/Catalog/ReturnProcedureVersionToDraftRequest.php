<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class ReturnProcedureVersionToDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('returnToDraft', $this->route('version')) ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
