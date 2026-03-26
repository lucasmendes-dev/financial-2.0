<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ticker' => ['required', 'string'],
            'type' => ['required', 'string', 'in:buy,sell'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'price_per_asset' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
