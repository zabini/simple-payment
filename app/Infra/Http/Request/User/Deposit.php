<?php

declare(strict_types=1);

namespace App\Infra\Http\Request\User;

use Hyperf\Validation\Request\FormRequest;

class Deposit extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
        ];
    }
}
