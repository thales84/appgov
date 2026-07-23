<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class PublishProcedureVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('publish', $this->route('version')) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
