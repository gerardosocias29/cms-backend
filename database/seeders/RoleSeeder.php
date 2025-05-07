<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Role, RoleModule};

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            1 => ['name' => 'superadmin', 'description' => ""],
            2 => ['name' => 'admin', 'description' => ""],
            3 => ['name' => 'staff', 'description' => ""],
        ];

        $roleModules = [
            1 => ['dashboard', 'patient-triage', 'queue', 'departments', 'users', 'settings'], // Superadmin
            2 => ['dashboard', 'patient-triage', 'departments', 'users'], // Admin
            3 => ['dashboard', 'queue'], // Staff
        ];

        foreach ($roles as $id => $roleData) {
            $role = Role::updateOrCreate(['id' => $id], $roleData);

            // Assign only allowed pages per role
            foreach ($roleModules[$id] as $page) {
                RoleModule::updateOrCreate([
                    'role_id' => $role->id,
                    'page' => $page,
                ], [
                    'description' => "Access to $page",
                ]);
            }
        }
    }
}
