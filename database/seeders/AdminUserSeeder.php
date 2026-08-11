<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Default admin login, documented in README.md. must_change_password
     * is true so the seeded password is forced to rotate on first login.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['username' => '7900000'],
            [
                'name' => 'W-Academy Admin',
                'phone' => '7900000',
                'password' => Hash::make('WAcademy@2026'),
                'locale' => 'dv',
                'must_change_password' => true,
                'is_active' => true,
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
