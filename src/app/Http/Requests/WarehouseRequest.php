<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $warehouse = $this->route('warehouse');

        return [
            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouses', 'code')
                    ->ignore($warehouse?->id),
            ],

            'address' => ['nullable', 'string'],

            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }
}
