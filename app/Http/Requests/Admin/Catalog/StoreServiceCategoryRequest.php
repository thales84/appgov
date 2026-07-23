<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domain\Catalog\Enums\ServiceColor;
use App\Domain\Catalog\Models\ServiceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ServiceCategory::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('service_categories', 'code')],
            'name_fr' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_fr' => ['nullable', 'string', 'max:1500'],
            'description_en' => ['nullable', 'string', 'max:1500'],
            'color_key' => ['required', Rule::enum(ServiceColor::class)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000'],
        ];
    }
}
