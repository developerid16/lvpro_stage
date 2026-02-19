<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddPaymentMethodRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'AxAccountNo' => ['required', 'string', 'max:50'],
            'ShoppingCartNo' => ['required', 'string', 'max:50'],
            'AXAIFUser' => ['required', 'string', 'max:50'],

            'Payment' => ['required', 'array'],
            'Payment.Amount' => ['required', 'numeric', 'min:0.01'],
            'Payment.PaymentMode' => ['required', 'string', 'in:PayNow'],
            'Payment.PaymentModeText' => ['required', 'string', 'in:RED DOT PAYNOW'],
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
