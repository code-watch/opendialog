<?php

namespace App\Http\Requests;

use App\Rules\IntentExists;

class ComponentConfigurationTestRequest extends ComponentConfigurationRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $originalRules = parent::rules();

        $rules = [
            'action_data' => ['bail', 'sometimes', 'array'],
            'action_data.attributes' => ['bail', 'sometimes', 'array'],
            'action_data.attributes.*' => ['bail', 'sometimes', 'string', 'nullable'],
            'action_data.intent_id' => ['bail', 'sometimes', new IntentExists()],
        ];

        $rules['component_id'] = $originalRules['component_id'];
        $rules['configuration'] = $originalRules['configuration'];
        $rules['configuration.app_url'] = $originalRules['configuration.app_url'] ?? [];
        $rules['configuration.webhook_url'] = $originalRules['configuration.webhook_url'] ?? [];

        return $rules;
    }
}
