<?php
namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        User::firstOrCreate(
            ['email' => 'admin@ecom.test'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password123'),
                'role_id'  => $adminRole ? $adminRole->id : null,
            ]
        );
    }
}
