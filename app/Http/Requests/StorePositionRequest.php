<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
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
            'asset_id' => ['nullable', 'string', 'exists:assets,id'],
            'asset_ticker' => ['required', 'string'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'avg_price' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
