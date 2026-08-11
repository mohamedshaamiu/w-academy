<?php

namespace Database\Factories;

use App\Models\AgreementTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgreementTemplate>
 */
class AgreementTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'version' => fake()->unique()->numberBetween(1, 100000),
            'title_dv' => '[DV CONTENT PENDING]',
            'title_en' => 'W-Academy Discipline Agreement',
            'body_dv' => '[DV CONTENT PENDING]',
            'body_en' => 'By signing this agreement you agree to the discipline framework.',
            'consent_clauses' => [
                ['key' => 'discipline_policy_acknowledged', 'label_dv' => '[DV CONTENT PENDING]', 'label_en' => 'I acknowledge the discipline policy.', 'required' => true],
                ['key' => 'photo_consent', 'label_dv' => '[DV CONTENT PENDING]', 'label_en' => 'I consent to photos being taken.', 'required' => false],
            ],
            'effective_from' => now()->toDateString(),
            'is_current' => false,
        ];
    }
}
