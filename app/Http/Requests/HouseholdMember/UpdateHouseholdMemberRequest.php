<?php

namespace App\Http\Requests\HouseholdMember;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHouseholdMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'exists:addresses,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{2}-\d{2}$/',
            ],
            'relationship_type_id' => ['required', 'exists:relationship_types,id'],
        ];
    }
}
