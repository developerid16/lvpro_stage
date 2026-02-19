<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddMerchandiseItemCartRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ShoppingCartNo' => ['required', 'string', 'max:50'],
            'ProdItemID' => ['required', 'string', 'max:50'],
            'ItemDesc' => ['required', 'string', 'max:255'],
            'Qty' => ['required', 'integer', 'min:1'],
            'AmountPerUnit' => ['required', 'numeric', 'min:0'],
            'IsAwardPoint' => ['required', 'integer', 'in:0,1'],
            'IsNonScmsTransaction' => ['required', 'boolean'],
            'Site' => ['required', 'string', 'size:2'],
            'Reference1' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Force API JSON validation response
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
