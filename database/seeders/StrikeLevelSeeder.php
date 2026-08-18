<?php

namespace Database\Seeders;

use App\Models\FrameworkStrikeLevel;
use Illuminate\Database\Seeder;

class StrikeLevelSeeder extends Seeder
{
    /**
     * The strike ladder is table-driven (see config('academy.
     * suspension_trigger_level')). The three levels' Dhivehi copy comes from
     * the customer's own discipline document ("customer documents/W
     * CDMY.pdf" §4, transcribed — see "customer documents/
     * TRANSCRIPTION-DV.md"), and the escalation flags now mirror that
     * document: a 3–5 minute time-out at strike 1, a parent meeting/call at
     * strike 2, suspension at strike 3, with the parent alerted at every
     * level.
     *
     * English translations were not supplied and SPEC.md §3.6 forbids
     * machine-translating, so the *_en columns stay pending. Levels beyond
     * 3 (possible if ACADEMY_SUSPENSION_TRIGGER_LEVEL is raised) have no
     * customer copy at all and stay fully pending.
     */
    public function run(): void
    {
        $suspensionTrigger = (int) config('academy.suspension_trigger_level');

        $customerCopy = [
            1 => [
                'label_dv' => 'ސްޓްރައިކް 1',
                'type_dv' => 'ފުރަތަމަ އިންޒާރު',
                'action_dv' => 'ކޯޗު ކުއްޖާ ހުއްޓުވައި އަނގަބަހުން ނަސޭހަތްދޭނެއެވެ. އަދި ވިސްނާދިނުމަށްޓަކައި 3-5 މިނެޓުގެ "ޓައިމް އައުޓް" ބައިންދައްޓައި، ޕްރެކްޓިސްތެރެއިން ބޭރުގައި ބައިންދާނެއެވެ.',
                'parent_role_dv' => 'ޕްރެކްޓިސް ނިމުމުން ކޯޗު ބެލެނިވެރިޔާއަށް މިކަން އަންގާނެއެވެ.',
                'timeout' => [3, 5],
                'meeting' => false,
            ],
            2 => [
                'label_dv' => 'ސްޓްރައިކް 2',
                'type_dv' => 'އެންމެ ފަހުގެ އިންޒާރު',
                'action_dv' => 'ނޭދެވޭ ސުލޫކު ތަކުރާރުވުން ނުވަތަ އާ އުސޫލަކާ ޚިލާފުވެއްޖެނަމަ، އެ ދުވަހުގެ ޕްރެކްޓިސް ސެޝަންގެ ބާކީ ބައިން ކުއްޖާ ވަކިކުރެވޭނެއެވެ.',
                'parent_role_dv' => 'ކުއްޖާގެ ސުލޫކު އިސްލާޙުކުރާނެ ސްޓްރެޓެޖީއަކަށް މަޝްވަރާކުރުމަށް ރަސްމީ ބައްދަލުވުމެއް ނުވަތަ ކޯލެއް ކުރެވޭނެއެވެ.',
                'timeout' => null,
                'meeting' => true,
            ],
            3 => [
                'label_dv' => 'ސްޓްރައިކް 3',
                'type_dv' => 'ސަސްޕެންޝަން',
                'action_dv' => 'ޖުމްލަ 3 އިންޒާރު ހަމަވުމުން އެކަޑަމީން ވަގުތީ ގޮތުން ސަސްޕެންޑް ކުރެވޭނެއެވެ. މި މުއްދަތުގައި ޕްރެކްޓިސްތަކަށް ނުވަތަ މެޗުތަކަށް ނާދެވޭނެއެވެ.',
                'parent_role_dv' => 'ގޭގައްޔާއި ސްކޫލުގައި ކުއްޖާގެ ސުލޫކު އިސްލާޙުކުރުމަށް ބެލެނިވެރިން ގޭގައި މަސައްކަތް ކުރަންޖެހޭނެއެވެ.',
                'timeout' => null,
                'meeting' => true,
            ],
        ];

        for ($level = 1; $level <= max(3, $suspensionTrigger); $level++) {
            $copy = $customerCopy[$level] ?? null;
            $timeout = $copy['timeout'] ?? null;

            FrameworkStrikeLevel::updateOrCreate(
                ['level' => $level],
                [
                    'label_dv' => $copy['label_dv'] ?? '[DV CONTENT PENDING]',
                    'label_en' => '[EN CONTENT PENDING]',
                    'type_dv' => $copy['type_dv'] ?? '[DV CONTENT PENDING]',
                    'type_en' => '[EN CONTENT PENDING]',
                    'action_dv' => $copy['action_dv'] ?? '[DV CONTENT PENDING]',
                    'action_en' => '[EN CONTENT PENDING]',
                    'parent_role_dv' => $copy['parent_role_dv'] ?? '[DV CONTENT PENDING]',
                    'parent_role_en' => '[EN CONTENT PENDING]',
                    'triggers_timeout' => $timeout !== null,
                    'timeout_minutes_min' => $timeout[0] ?? null,
                    'timeout_minutes_max' => $timeout[1] ?? null,
                    'triggers_parent_alert' => true,
                    'triggers_meeting' => $copy['meeting'] ?? ($level >= $suspensionTrigger - 1),
                    'triggers_suspension' => $level === $suspensionTrigger,
                    'sort_order' => $level,
                    'is_active' => true,
                ]
            );
        }
    }
}
