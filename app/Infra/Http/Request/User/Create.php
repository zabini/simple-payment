<?php

declare(strict_types=1);

namespace App\Infra\Http\Request\User;

use Hyperf\Validation\Request\FormRequest;

class Create extends FormRequest
{

    /**
     * @return boolean
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string',
            'kind' => 'required|string',
            'document_type' => 'required|string',
            'document' => 'required|string',
            'mail' => 'required|string',
            'password' => 'required|string',
        ];
    }
}
