<?php

namespace Database\Seeders;

use App\Models\FrameworkPillar;
use Illuminate\Database\Seeder;

class FrameworkPillarSeeder extends Seeder
{
    /**
     * The 4 life pillars are fixed by the academy's behaviour framework
     * (home, school, religion, sport). The descriptive copy has not been
     * supplied, so it is seeded as pending content rather than fabricated.
     */
    public function run(): void
    {
        $pillars = [
            ['code' => 'home', 'icon' => 'home', 'sort_order' => 1],
            ['code' => 'school', 'icon' => 'academic-cap', 'sort_order' => 2],
            ['code' => 'religion', 'icon' => 'moon', 'sort_order' => 3],
            ['code' => 'sport', 'icon' => 'trophy', 'sort_order' => 4],
        ];

        foreach ($pillars as $pillar) {
            FrameworkPillar::updateOrCreate(
                ['code' => $pillar['code']],
                [
                    'name_dv' => '[DV CONTENT PENDING]',
                    'name_en' => '[EN CONTENT PENDING]',
                    'description_dv' => '[DV CONTENT PENDING]',
                    'description_en' => '[EN CONTENT PENDING]',
                    'icon' => $pillar['icon'],
                    'sort_order' => $pillar['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
