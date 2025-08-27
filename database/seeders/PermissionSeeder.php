<?php
namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage_users',
            'manage_products',
            'manage_orders',
            'view_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole    = Role::where('name', 'admin')->first();
        $managerRole  = Role::where('name', 'manager')->first();
        $customerRole = Role::where('name', 'customer')->first();

        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::all()->pluck('id'));
        }

        if ($managerRole) {
            $managerRole->permissions()->sync(
                Permission::whereIn('name', ['manage_products', 'manage_orders', 'view_reports'])->pluck('id')
            );
        }

        if ($customerRole) {
            $customerRole->permissions()->sync([]); // customers don’t get special perms
        }
    }
}
