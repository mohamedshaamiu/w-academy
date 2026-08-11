<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. DemoDataSeeder is excluded from
     * production runs — call it explicitly in local development instead.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            FrameworkPillarSeeder::class,
            StrikeLevelSeeder::class,
            AgreementTemplateSeeder::class,
            AdminUserSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
