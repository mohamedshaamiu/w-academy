<?php

namespace Database\Seeders;

use App\Models\FrameworkPillar;
use Illuminate\Database\Seeder;

class FrameworkPillarSeeder extends Seeder
{
    /**
     * The 4 life pillars are fixed by the academy's behaviour framework and
     * named in SPEC.md §1: home, school, religion, sport.
     *
     * DEMO COPY — CLIENT TO CONFIRM. The pillar NAMES are taken from SPEC.md
     * §1 and are factual. The DESCRIPTIONS were authored so the public site
     * reads as a finished page for the demo; they were not supplied by
     * W-Academy and SPEC.md §3.6 requires customer confirmation before
     * go-live. Tracked as BACKLOG-NEW.md NEW-5.
     *
     * To return to the strictly-pending position, replace the four
     * `description_*` values with '[DV CONTENT PENDING]' / '[EN CONTENT
     * PENDING]' and re-run this seeder — it is idempotent on `code`.
     */
    public function run(): void
    {
        $pillars = [
            [
                'code' => 'home',
                'icon' => 'home',
                'sort_order' => 1,
                'name_dv' => 'ގޭގެ ދިރިއުޅުން',
                'name_en' => 'Home',
                'description_dv' => 'ގޭގައި އިޙްތިރާމާއި ޒިންމާދާރުކަން — ދަރިވަރު އާއިލާއާ މުޢާމަލާތްކުރާ ގޮތާއި، ގޭގެ މަސައްކަތްތަކާއި، އަމިއްލަ ތަކެތި ބަލަހައްޓާ ގޮތެވެ.',
                'description_en' => 'Respect and responsibility at home — how a player treats their family, their chores and their own belongings.',
            ],
            [
                'code' => 'school',
                'icon' => 'academic-cap',
                'sort_order' => 2,
                'name_dv' => 'ސްކޫލް',
                'name_en' => 'School',
                'description_dv' => 'ސްކޫލަށް ހާޒިރުވުމާއި، މަސައްކަތްކުރުމާއި، އަޚްލާޤު. ކްލާހަށް ހާޒިރުނުވާ ދަރިވަރަކު ދަނޑަށް ތައްޔާރެއް ނޫނެވެ.',
                'description_en' => 'Attendance, effort and conduct at school. A player who is not showing up for class is not ready for the pitch.',
            ],
            [
                'code' => 'religion',
                'icon' => 'moon',
                'sort_order' => 3,
                'name_dv' => 'ދީން',
                'name_en' => 'Religion',
                'description_dv' => 'ނަމާދާއި، އަދަބު އަޚްލާޤާއި، ތެދުވެރިކަން. ދީނުން ލާޒިމުކުރާ އަޚްލާޤުގެ މިންގަނޑު އެކަޑަމީންވެސް ބަލައެވެ.',
                'description_en' => 'Prayer, manners and honesty. The academy expects the same standard of character that faith asks for.',
            ],
            [
                'code' => 'sport',
                'icon' => 'trophy',
                'sort_order' => 4,
                'name_dv' => 'ކުޅިވަރު',
                'name_en' => 'Sport',
                'description_dv' => 'ސާބިތުކަމާއި، އިންސާފުވެރި ކުޅުމާއި، ކޯޗުންނާއި ޓީމުގެ އެހެން ކުޅުންތެރިންނާއި އޮފިޝަލުންނަށް އިޙްތިރާމްކުރުން — ފަރިތަކުރުމުގައްޔާއި މެޗުތަކުގައި.',
                'description_en' => 'Commitment, fair play and respect for coaches, teammates and officials — in training and in matches.',
            ],
        ];

        foreach ($pillars as $pillar) {
            FrameworkPillar::updateOrCreate(
                ['code' => $pillar['code']],
                [
                    'name_dv' => $pillar['name_dv'],
                    'name_en' => $pillar['name_en'],
                    'description_dv' => $pillar['description_dv'],
                    'description_en' => $pillar['description_en'],
                    'icon' => $pillar['icon'],
                    'sort_order' => $pillar['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
