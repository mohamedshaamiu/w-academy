<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $userId = $this->route('guardian')->user_id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'regex:/^[79]\d{6}$/', Rule::unique('users', 'phone')->ignore($userId)],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'address' => ['required', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:100'],
        ];
    }
}
