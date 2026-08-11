<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgreementTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'version' => ['required', 'integer', 'min:1', 'unique:agreement_templates,version'],
            'title_dv' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'body_dv' => ['required', 'string'],
            'body_en' => ['required', 'string'],
            'effective_from' => ['required', 'date'],
            'consent_clauses' => ['required', 'array', 'min:1'],
            'consent_clauses.*.key' => ['required', 'string', 'max:50'],
            'consent_clauses.*.label_dv' => ['required', 'string', 'max:500'],
            'consent_clauses.*.label_en' => ['required', 'string', 'max:500'],
            'consent_clauses.*.required' => ['boolean'],
        ];
    }
}
