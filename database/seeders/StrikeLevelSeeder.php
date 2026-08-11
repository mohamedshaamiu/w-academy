<?php

namespace Database\Seeders;

use App\Models\FrameworkStrikeLevel;
use Illuminate\Database\Seeder;

class StrikeLevelSeeder extends Seeder
{
    /**
     * The strike ladder is entirely table-driven (see config('academy.
     * suspension_trigger_level')). The source table's actual type/action/
     * parent-role copy has not been supplied, so those columns are seeded
     * as pending content. The row count and escalation flags are a
     * reasonable placeholder structure, not fabricated policy text.
     */
    public function run(): void
    {
        $suspensionTrigger = (int) config('academy.suspension_trigger_level');

        for ($level = 1; $level <= max(3, $suspensionTrigger); $level++) {
            $triggersSuspension = $level === $suspensionTrigger;
            $triggersMeeting = $triggersSuspension || $level === $suspensionTrigger - 1;
            $triggersTimeout = $level >= 2 && ! $triggersSuspension;

            FrameworkStrikeLevel::updateOrCreate(
                ['level' => $level],
                [
                    'label_dv' => '[DV CONTENT PENDING]',
                    'label_en' => '[EN CONTENT PENDING]',
                    'type_dv' => '[DV CONTENT PENDING]',
                    'type_en' => '[EN CONTENT PENDING]',
                    'action_dv' => '[DV CONTENT PENDING]',
                    'action_en' => '[EN CONTENT PENDING]',
                    'parent_role_dv' => '[DV CONTENT PENDING]',
                    'parent_role_en' => '[EN CONTENT PENDING]',
                    'triggers_timeout' => $triggersTimeout,
                    'timeout_minutes_min' => $triggersTimeout ? 5 : null,
                    'timeout_minutes_max' => $triggersTimeout ? 10 : null,
                    'triggers_parent_alert' => true,
                    'triggers_meeting' => $triggersMeeting,
                    'triggers_suspension' => $triggersSuspension,
                    'sort_order' => $level,
                    'is_active' => true,
                ]
            );
        }
    }
}
