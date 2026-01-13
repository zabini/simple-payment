<?php

declare(strict_types=1);

namespace App\Infra\Http\Request\User;

use Hyperf\Validation\Request\FormRequest;

class Deposit extends FormRequest
{

    /**
     * @return boolean
     */
    public function authorize(): bool
    {
        return true;
    }

    /** @inheritDoc */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
        ];
    }

    /** @inheritDoc */
    public function messages(): array
    {
        return [
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
        ];
    }
}
