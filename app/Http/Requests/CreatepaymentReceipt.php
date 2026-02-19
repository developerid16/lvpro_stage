<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatepaymentReceipt extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'AxAccountNo'   => ['required', 'string', 'max:50'],
            'ShoppingCartNo'=> ['required', 'string', 'max:50'],
            'Site'          => ['required', 'string', 'size:2', 'in:TP,TM,YS,JR,MF,PG,CK,IN'],
            'Warehouse'     => ['required', 'string', 'size:2', 'in:TP,TM,YS,JR,MF,PG,CK,IN'],
            'LoginName'     => ['required', 'string', 'max:50'],
            'ReceiptId'     => ['required', 'string', 'regex:/^MB8[0-9]+$/'],
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
