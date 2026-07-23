<?php

namespace App\Domain\Applications\Services;

use App\Domain\Catalog\Models\ProcedureVersion;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValidateFormResponses
{
    /**
     * Validate array of responses against the form fields configured in a ProcedureVersion.
     *
     * @throws ValidationException
     */
    public function execute(ProcedureVersion $version, array $responses, bool $isSubmitting = false): array
    {
        $version->loadMissing('formFields');

        $rules = [];
        $attributes = [];

        foreach ($version->formFields as $field) {
            $fieldRules = [];

            if ($isSubmitting && $field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            match ($field->field_type) {
                'number' => $fieldRules[] = 'numeric',
                'integer' => $fieldRules[] = 'integer',
                'boolean' => $fieldRules[] = 'boolean',
                'date' => $fieldRules[] = 'date_format:Y-m-d',
                'email' => $fieldRules[] = 'email',
                'phone' => $fieldRules[] = 'string|max:30',
                'select', 'radio' => $this->addSelectRule($field, $fieldRules),
                default => $fieldRules[] = 'string',
            };

            $rules[$field->code] = implode('|', array_filter($fieldRules));
            $attributes[$field->code] = $field->label_fr;
        }

        $validator = Validator::make($responses, $rules, [], $attributes);

        return $validator->validate();
    }

    private function addSelectRule($field, array &$fieldRules): void
    {
        $config = $field->configuration ?? [];
        $options = $config['options'] ?? [];

        if (! empty($options)) {
            $allowedValues = array_column($options, 'value');
            if (! empty($allowedValues)) {
                $fieldRules[] = 'in:'.implode(',', $allowedValues);

                return;
            }
        }

        $fieldRules[] = 'string';
    }
}
