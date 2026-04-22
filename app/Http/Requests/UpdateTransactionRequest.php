<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'asset_id' => ['sometimes', 'string', 'exists:assets,id'],
            'type' => ['sometimes', 'string', 'in:buy,sell'],
            'quantity' => ['sometimes', 'numeric', 'min:0.01'],
            'price_per_asset' => ['sometimes', 'numeric', 'min:0.01'],
            'total' => ['sometimes', 'numeric', 'min:0.01'],
            'executed_at' => ['sometimes', 'date'],
        ];
    }
}
