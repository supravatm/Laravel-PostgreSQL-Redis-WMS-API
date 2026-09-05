<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class TransferStockRequest extends FormRequest
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
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],

            'source_location_id' => [
                'required',
                'integer',
                'exists:locations,id',
            ],

            'destination_location_id' => [
                'required',
                'integer',
                'exists:locations,id',
                Rule::notIn([
                    $this->input('source_location_id'),
                ]),
            ],

            'quantity' => ['required', 'integer', 'min:1'],

            'reference_number' => [
                'required',
                'string',
                'max:100',
                'unique:stock_movements,reference_number',
            ],
        ];
    }
}
