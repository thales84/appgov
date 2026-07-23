<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class SubmitProcedureVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('submitForReview', $this->route('version')) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
