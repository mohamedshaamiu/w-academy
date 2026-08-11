<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignAgreementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signatory_name' => ['required', 'string', 'min:3', 'max:150'],
            'consents' => ['required', 'array'],
            'consents.*' => ['boolean'],
            'signature_image' => ['nullable', 'string'],
        ];
    }
}
