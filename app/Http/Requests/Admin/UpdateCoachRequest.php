<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $userId = $this->route('coach')->user_id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'regex:/^[79]\d{6}$/', Rule::unique('users', 'phone')->ignore($userId)],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'specialisation' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'joined_on' => ['required', 'date'],
        ];
    }
}
