<?php

declare(strict_types=1);

namespace App\Infra\Http\Request\User;

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
            'full_name' => 'required|string',
            'kind' => 'required|string',
            'document_type' => 'required|string',
            'document' => 'required|string',
            'email' => 'required|string',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required.',
            'full_name.string' => 'Full name must be a string.',
            'kind.required' => 'Kind is required.',
            'kind.string' => 'Kind must be a string.',
            'document_type.required' => 'Document type is required.',
            'document_type.string' => 'Document type must be a string.',
            'document.required' => 'Document is required.',
            'document.string' => 'Document must be a string.',
            'email.required' => 'email is required.',
            'email.string' => 'email must be a string.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
        ];
    }
}
