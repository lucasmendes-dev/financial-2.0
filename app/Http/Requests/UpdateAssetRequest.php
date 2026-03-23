<?php

namespace App\Http\Requests;

use App\Models\Asset;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
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
        $asset = $this->route('asset');
        $id = $asset instanceof Asset ? $asset->id : $asset;

        return [
            'ticker' => ['sometimes', 'required', 'string', Rule::unique('assets', 'ticker')->ignore($id)],
            'name' => ['sometimes', 'required', 'string'],
            'type' => ['sometimes', 'required', Rule::in(['stock', 'fii'])],
        ];
    }
}
