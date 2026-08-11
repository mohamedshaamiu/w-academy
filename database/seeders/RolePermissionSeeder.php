<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('permissions')->delete();
        DB::table('roles')->delete();

        $permissions = [
            'student.manage',
            'squad.manage',
            'coach.manage',
            'guardian.manage',
            'session.manage',
            'agreement.template.manage',
            'framework.manage',
            'credential.issue',
            'report.view',
            'audit.view',
            'session.view',
            'session.start',
            'session.complete',
            'attendance.mark',
            'squad.view.own',
            'student.view.own_squad',
            'framework.view',
            'student.view.own',
            'agreement.sign',
            'session.view.own_child',
            'attendance.view.own_child',
            'profile.edit.own',
            'schedule.view.own',
            'attendance.view.own',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $coach = Role::firstOrCreate(['name' => 'coach', 'guard_name' => 'web']);
        $coach->syncPermissions([
            'session.view',
            'session.start',
            'session.complete',
            'attendance.mark',
            'squad.view.own',
            'student.view.own_squad',
            'framework.view',
        ]);

        $guardian = Role::firstOrCreate(['name' => 'guardian', 'guard_name' => 'web']);
        $guardian->syncPermissions([
            'student.view.own',
            'agreement.sign',
            'session.view.own_child',
            'attendance.view.own_child',
            'framework.view',
            'profile.edit.own',
        ]);

        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions([
            'schedule.view.own',
            'attendance.view.own',
            'framework.view',
            'profile.edit.own',
        ]);
    }
}
