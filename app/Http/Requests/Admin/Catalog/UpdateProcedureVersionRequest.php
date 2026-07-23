<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domain\Catalog\Enums\FormFieldType;
use App\Domain\Catalog\Enums\ProcedureRuleType;
use App\Domain\Catalog\Enums\ProcedureStepType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProcedureVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('version')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $stepCodes = collect($this->input('steps', []))->pluck('code')->filter()->all();

        return [
            'title_fr' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'summary_fr' => ['required', 'string', 'max:1500'],
            'summary_en' => ['required', 'string', 'max:1500'],
            'description_fr' => ['nullable', 'string', 'max:10000'],
            'description_en' => ['nullable', 'string', 'max:10000'],
            'eligibility_fr' => ['nullable', 'string', 'max:10000'],
            'eligibility_en' => ['nullable', 'string', 'max:10000'],
            'legal_basis_fr' => ['nullable', 'string', 'max:2000'],
            'legal_basis_en' => ['nullable', 'string', 'max:2000'],
            'effective_from' => ['nullable', 'date_format:Y-m-d'],
            'is_demo' => ['required', 'boolean'],

            'steps' => ['present', 'array', 'max:50'],
            'steps.*.public_id' => ['nullable', 'string', 'size:26'],
            'steps.*.code' => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9_-]+$/', 'distinct'],
            'steps.*.name_fr' => ['required', 'string', 'max:255'],
            'steps.*.name_en' => ['required', 'string', 'max:255'],
            'steps.*.description_fr' => ['nullable', 'string', 'max:2000'],
            'steps.*.description_en' => ['nullable', 'string', 'max:2000'],
            'steps.*.step_type' => ['required', Rule::enum(ProcedureStepType::class)],
            'steps.*.is_required' => ['required', 'boolean'],

            'fields' => ['present', 'array', 'max:100'],
            'fields.*.public_id' => ['nullable', 'string', 'size:26'],
            'fields.*.step_code' => ['required', 'string', Rule::in($stepCodes)],
            'fields.*.code' => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9_-]+$/', 'distinct'],
            'fields.*.label_fr' => ['required', 'string', 'max:255'],
            'fields.*.label_en' => ['required', 'string', 'max:255'],
            'fields.*.help_fr' => ['nullable', 'string', 'max:1500'],
            'fields.*.help_en' => ['nullable', 'string', 'max:1500'],
            'fields.*.field_type' => ['required', Rule::enum(FormFieldType::class)],
            'fields.*.is_required' => ['required', 'boolean'],
            'fields.*.options_fr' => ['nullable', 'string', 'max:5000'],
            'fields.*.options_en' => ['nullable', 'string', 'max:5000'],

            'documents' => ['present', 'array', 'max:100'],
            'documents.*.public_id' => ['nullable', 'string', 'size:26'],
            'documents.*.step_code' => ['nullable', 'string', Rule::in($stepCodes)],
            'documents.*.code' => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9_-]+$/', 'distinct'],
            'documents.*.name_fr' => ['required', 'string', 'max:255'],
            'documents.*.name_en' => ['required', 'string', 'max:255'],
            'documents.*.description_fr' => ['nullable', 'string', 'max:1500'],
            'documents.*.description_en' => ['nullable', 'string', 'max:1500'],
            'documents.*.is_required' => ['required', 'boolean'],

            'rules' => ['present', 'array', 'max:100'],
            'rules.*.public_id' => ['nullable', 'string', 'size:26'],
            'rules.*.code' => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9_-]+$/', 'distinct'],
            'rules.*.name_fr' => ['required', 'string', 'max:255'],
            'rules.*.name_en' => ['required', 'string', 'max:255'],
            'rules.*.description_fr' => ['required', 'string', 'max:2000'],
            'rules.*.description_en' => ['required', 'string', 'max:2000'],
            'rules.*.rule_type' => ['required', Rule::enum(ProcedureRuleType::class)],

            'fees' => ['present', 'array', 'max:100'],
            'fees.*.public_id' => ['nullable', 'string', 'size:26'],
            'fees.*.step_code' => ['nullable', 'string', Rule::in($stepCodes)],
            'fees.*.code' => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9_-]+$/', 'distinct'],
            'fees.*.label_fr' => ['required', 'string', 'max:255'],
            'fees.*.label_en' => ['required', 'string', 'max:255'],
            'fees.*.description_fr' => ['nullable', 'string', 'max:1500'],
            'fees.*.description_en' => ['nullable', 'string', 'max:1500'],
            'fees.*.amount_minor' => ['required', 'integer', 'min:0'],
            'fees.*.currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'fees.*.minor_unit_exponent' => ['required', 'integer', 'min:0', 'max:3'],
            'fees.*.is_mandatory' => ['required', 'boolean'],
            'fees.*.due_when_fr' => ['nullable', 'string', 'max:255'],
            'fees.*.due_when_en' => ['nullable', 'string', 'max:255'],
            'fees.*.legal_basis_fr' => ['nullable', 'string', 'max:2000'],
            'fees.*.legal_basis_en' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
