<?php

namespace App\Http\Requests\Admin;

use App\Services\StudentRegistrationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'index_number' => ['required', 'regex:/^[A-Za-z0-9\-]{3,20}$/', Rule::unique('students', 'index_number')],
            'full_name' => ['required', 'string', 'max:150'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'class_level' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:4096'],

            'guardians' => ['required', 'array', 'min:1'],
            'guardians.*.mode' => ['required', 'in:existing,new'],
            'guardians.*.guardian_id' => ['required_if:guardians.*.mode,existing', 'nullable', 'integer', 'exists:guardians,id'],
            'guardians.*.name' => ['required_if:guardians.*.mode,new', 'nullable', 'string', 'max:150'],
            'guardians.*.phone' => ['required_if:guardians.*.mode,new', 'nullable', 'regex:/^[79]\d{6}$/'],
            'guardians.*.address' => ['required_if:guardians.*.mode,new', 'nullable', 'string', 'max:255'],
            'guardians.*.relationship' => ['required', 'string', 'max:50'],
            'guardians.*.is_primary' => ['required', 'boolean'],
            'guardians.*.receives_alerts' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $age = StudentRegistrationService::ageAsOfToday((string) $this->input('date_of_birth'));

            if ($this->filled('date_of_birth') && ($age < 5 || $age > 18)) {
                $validator->errors()->add('date_of_birth', __('validation.custom.date_of_birth.age_range'));
            }
        });
    }
}
