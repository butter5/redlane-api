<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExchangeRateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermissionTo('manage_currencies');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_currency_id' => ['required', 'exists:currencies,id'],
            'to_currency_id' => ['required', 'exists:currencies,id', 'different:from_currency_id'],
            'rate' => ['required', 'numeric', 'min:0.000001', 'max:9999.999999'],
            'effective_date' => ['required', 'date'],
            'source' => ['required', 'in:manual,api_fetched'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'from_currency_id.exists' => 'The source currency is invalid.',
            'to_currency_id.exists' => 'The target currency is invalid.',
            'to_currency_id.different' => 'The target currency must be different from the source currency.',
            'rate.min' => 'The exchange rate must be greater than zero.',
            'source.in' => 'The source must be either manual or api_fetched.',
        ];
    }
}
