<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDutyCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermissionTo('manage_duty_categories');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $dutyCategoryId = $this->route('duty_category');
        
        return [
            'code' => ['sometimes', 'string', 'max:255', Rule::unique('duty_categories')->ignore($dutyCategoryId)],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon_name' => ['nullable', 'string', 'max:255'],
            'calculation_method_type_id' => ['sometimes', 'exists:calculation_method_types,id'],
            'duty_rate' => ['sometimes', 'numeric', 'min:0', 'max:9999.9999'],
            'duty_unit_type_id' => ['nullable', 'exists:unit_types,id'],
            'exemption_quantity' => ['nullable', 'numeric', 'min:0'],
            'exemption_unit_type_id' => ['nullable', 'exists:unit_types,id'],
            'is_active' => ['boolean'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'A duty category with this code already exists.',
            'calculation_method_type_id.exists' => 'The selected calculation method is invalid.',
            'duty_unit_type_id.exists' => 'The selected duty unit type is invalid.',
            'exemption_unit_type_id.exists' => 'The selected exemption unit type is invalid.',
            'effective_to.after_or_equal' => 'The effective end date must be after or equal to the start date.',
        ];
    }
}
