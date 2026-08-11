<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSquadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name_dv' => ['required', 'string', 'max:100'],
            'name_en' => ['required', 'string', 'max:100'],
            'age_group' => ['required', 'string', 'max:30'],
            'head_coach_id' => ['nullable', 'integer', 'exists:coaches,id'],
            'venue_dv' => ['nullable', 'string', 'max:150'],
            'venue_en' => ['nullable', 'string', 'max:150'],
            'training_days' => ['required', 'array', 'min:1'],
            'training_days.*' => ['integer', 'between:1,7'],
            'default_start_time' => ['nullable', 'date_format:H:i'],
            'default_end_time' => ['nullable', 'date_format:H:i', 'after:default_start_time'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }
}
