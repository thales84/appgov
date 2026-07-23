<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class CreateProcedureVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('createVersion', $this->route('service')) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
