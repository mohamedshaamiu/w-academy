<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'regex:/^[79]\d{6}$/', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'specialisation' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'joined_on' => ['required', 'date'],
        ];
    }
}
