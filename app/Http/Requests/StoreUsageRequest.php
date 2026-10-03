<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUsageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 
     */
    protected function prepareForValidation(): void
    {
        // Accept the key from the header or the body
        $this->merge(['idempotency_key' => $this->header('Idempotency-Key', $this->input('idempotency_key'))]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id'     => ['required', 'integer'],
            'units'           => ['required', 'integer', 'min:1', 'max:1000000000'],
            'usage_date'      => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'idempotency_key' => ['required', 'string', 'max:64'],
        ];
    }
}
