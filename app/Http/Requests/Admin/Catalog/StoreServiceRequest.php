<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domain\Catalog\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Service::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'organization_public_id' => ['nullable', 'string', 'size:26', Rule::exists('organizations', 'public_id')->where('is_active', true)],
            'category_public_id' => ['required', 'string', 'size:26', Rule::exists('service_categories', 'public_id')->where('is_active', true)],
            'code' => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('services', 'code')],
            'name_fr' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_fr' => ['required', 'string', 'max:1500'],
            'description_en' => ['required', 'string', 'max:1500'],
        ];
    }
}
