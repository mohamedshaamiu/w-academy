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
     * The Dhivehi descriptions are the customer's own words, transcribed
     * from "customer documents/W CDMY.pdf" §2 (the four pillars of the code
     * of conduct) — see "customer documents/TRANSCRIPTION-DV.md" for
     * provenance.
     *
     * DEMO COPY — CLIENT TO CONFIRM. The ENGLISH descriptions were authored
     * for the demo before the customer document arrived; they are close in
     * spirit but are not translations of the Dhivehi and must be confirmed
     * or replaced by W-Academy before go-live (SPEC.md §3.6 forbids
     * machine-translating the supplied Dhivehi). Tracked as BACKLOG-NEW.md
     * NEW-5/NEW-7.
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
                'description_dv' => 'މައިންބަފައިންނާއި ބެލެނިވެރިންގެ ބަސްއެހުމާއި، އަދަބުވެރިކަމާއެކު ވާހަކަ ދެއްކުމުގައްޔާއި، ގޭތެރޭގެ ކަންކަމުގައި އެހީތެރިވުމާ މައިންބަފައިންނާއި ގޭގެ އިސްމީހުންގެ އިރުޝާދުތަކަށް ތަބާވާންޖެހޭނެއެވެ.',
                'description_en' => 'Respect and responsibility at home — how a player treats their family, their chores and their own belongings.',
            ],
            [
                'code' => 'school',
                'icon' => 'academic-cap',
                'sort_order' => 2,
                'name_dv' => 'ސްކޫލް',
                'name_en' => 'School',
                'description_dv' => 'ދަރިވަރުން އިސްކަންދޭ ކަންކަމުގެ ތެރެއިން އެންމެ އިސްކަމެއް އަބަދުވެސް ދޭންވާނީ ސްކޫލަށެވެ. ޓީޗަރުންގެ ބަސްއެހުމާއި، ހަވާލުކުރެވޭ ހުރިހާ މަސައްކަތާ އަދި ފިލާވަޅެއް ފުރިހަމަކުރުމާއި، ސްކޫލްގެ ހަރަކާތްތަކުގައި ރަނގަޅު އަޚްލާޤާއި އަދި ސުލޫކެއް ބަހައްޓަންވާނެއެވެ.',
                'description_en' => 'Attendance, effort and conduct at school. A player who is not showing up for class is not ready for the pitch.',
            ],
            [
                'code' => 'religion',
                'icon' => 'moon',
                'sort_order' => 3,
                'name_dv' => 'ދީން',
                'name_en' => 'Religion',
                'description_dv' => 'ދިރިއުޅުމުގެ ހުރިހާ މުޢާމަލާތުގައި އިސްލާމީ ރަނގަޅު އަޚްލާޤު ދެމެހެއްޓުން. މީގެ ތެރޭގައި އެހެންމީހުން ތަކާއި މުޢާމަލާތުގައި، މަޑުމައިތިރި ބަސްމަގު ބޭނުންކުރުމާއި، ހިނިތުންވުމާއި، އޯގާތެރިކަން ދެއްކުން ހިމެނެއެވެ.',
                'description_en' => 'Prayer, manners and honesty. The academy expects the same standard of character that faith asks for.',
            ],
            [
                'code' => 'sport',
                'icon' => 'trophy',
                'sort_order' => 4,
                'name_dv' => 'ކުޅިވަރު',
                'name_en' => 'Sport',
                'description_dv' => 'ޓްރެއިނިންގ ސެޝަންތަކާއި މެޗުތަކުގައި ކޯޗުންގެ އިރުޝާދުތަކަށް ފުރިހަމަ ފަރުވާއާ އެކީގައި ތަބާވުމާއި، ޓީމުގެ ކުޅުންތެރިންނަށާއި އިދިކޮޅު ކުޅުންތެރިންނަށް އިޙްތިރާމްކުރަންވާނެއެވެ.',
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
