<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'approve booking',
            'reject booking',
            'manage resources',
            'manage users',
            'view booking request',
            'view all booking logs',
            'request booking',
            'view own booking logs',
            'view available resources',
            'view booking status',
            'cancel booking',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api'
            ]);
        }

        $student = Role::firstOrCreate([
            'name' => 'student',
            'guard_name' => 'api'
        ]);
        $student->syncPermissions([
            'request booking',
            'view own booking logs',
            'view available resources',
            'view booking status',
            'cancel booking'
        ]);

        $faculty = Role::firstOrCreate([
            'name' => 'faculty',
            'guard_name' => 'api'
        ]);
        $faculty->syncPermissions([
            'request booking',
            'view own booking logs',
            'view available resources',
            'view booking status',
            'cancel booking'
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api'
        ]);
        $admin->syncPermissions([
            'approve booking',
            'reject booking',
            'manage resources',
            'manage users',
            'view booking request',
            'view all booking logs',
        ]);

        $computerLabSupervisor = Role::firstOrCreate([
            'name' => 'computer_lab_supervisor',
            'guard_name' => 'api'
        ]);
        $computerLabSupervisor->syncPermissions([
            'manage resources',
            'view booking request',
            'view all booking logs',
        ]);

        $librarySupervisor = Role::firstOrCreate([
            'name' => 'library_supervisor',
            'guard_name' => 'api'
        ]);
        $librarySupervisor->syncPermissions([
            'manage resources',
            'view booking request',
            'view all booking logs',
        ]);

        $venueSupervisor = Role::firstOrCreate([
            'name' => 'venue_hall_supervisor',
            'guard_name' => 'api'
        ]);
        $venueSupervisor->syncPermissions([
            'manage resources',
            'view booking request',
            'view all booking logs',
        ]);

        $sportsEquipmentSupervisor = Role::firstOrCreate([
            'name' => 'sports_equipment_supervisor',
            'guard_name' => 'api'
        ]);
        $sportsEquipmentSupervisor->syncPermissions([
            'manage resources',
            'view booking request',
            'view all booking logs',
        ]);
    }
}
