<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class StartApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCitizen() ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
