<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class UsersRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'kelola absen',
            'kelola gaji',
            'kelola gudang',
        ];

        // Create permissions in the database
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $managerRole = Role::create(['name' => 'Manager']);
        $adminGudangRole = Role::create(['name' => 'Admin Gudang']);
        $staffRole = Role::create(['name' => 'Staff']);

        // Assign all permissions to manager
        $managerRole->givePermissionTo($permissions);

        // Assign a subset of permissions to admin gudang
        $adminGudangPermissions = [
            'kelola gudang',
            'kelola absen',
        ];
        $adminGudangRole->givePermissionTo($adminGudangPermissions);

        // Assign a subset of permissions to admin gudang
        $staffPermissions = [
            'kelola absen',
        ];
        $staffRole->givePermissionTo($staffPermissions);

        // Create users by role
        $managerUser = User::create([
            'name' => 'Manager',
            'nik' => '001',
            'password' => bcrypt('admin123'),
        ]);
        $managerUser->assignRole($managerRole);

        $adminGudangUser = User::create([
            'name' => 'Admin Gudang',
            'nik' => '002',
            'password' => bcrypt('admin123'),
        ]);
        $adminGudangUser->assignRole($adminGudangRole);

        $staffUser = User::create([
            'name' => 'Staff',
            'nik' => '003',
            'password' => bcrypt('admin123'),
        ]);
        $staffUser->assignRole($staffRole);
    }
}
