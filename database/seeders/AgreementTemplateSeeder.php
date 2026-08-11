<?php

namespace Database\Seeders;

use App\Models\AgreementTemplate;
use Illuminate\Database\Seeder;

class AgreementTemplateSeeder extends Seeder
{
    /**
     * The discipline agreement's actual legal body text has not been
     * supplied, so it is seeded as pending content. The clause structure
     * (one acknowledgement, one consent-to-photo) is a reasonable
     * placeholder shape, not fabricated legal text.
     */
    public function run(): void
    {
        AgreementTemplate::updateOrCreate(
            ['version' => 1],
            [
                'title_dv' => '[DV CONTENT PENDING]',
                'title_en' => '[EN CONTENT PENDING]',
                'body_dv' => '[DV CONTENT PENDING]',
                'body_en' => '[EN CONTENT PENDING]',
                'consent_clauses' => [
                    [
                        'key' => 'discipline_policy_acknowledged',
                        'label_dv' => '[DV CONTENT PENDING]',
                        'label_en' => '[EN CONTENT PENDING]',
                        'required' => true,
                    ],
                    [
                        'key' => 'photo_consent',
                        'label_dv' => '[DV CONTENT PENDING]',
                        'label_en' => '[EN CONTENT PENDING]',
                        'required' => true,
                    ],
                ],
                'effective_from' => now()->toDateString(),
                'is_current' => true,
            ]
        );
    }
}
