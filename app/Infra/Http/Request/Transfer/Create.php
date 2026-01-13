<?php

declare(strict_types=1);

namespace App\Infra\Http\Request\Transfer;

use Hyperf\Validation\Request\FormRequest;

class Create extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payer' => 'required|string',
            'payee' => 'required|string',
            'amount' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'payer.required' => 'Payer is required.',
            'payer.string' => 'Payer must be a string.',
            'payee.required' => 'Payee is required.',
            'payee.string' => 'Payee must be a string.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
        ];
    }
}
