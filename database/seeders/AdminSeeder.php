<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Credential;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('role', 'admin')->first();

        $admin = User::create([
            'role_id' => $adminRole->id,
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'dob' => '1990-01-01',
            'address' => '123 Admin Street',
            'gender' => 'other',
            'nationality' => 'Global',
        ]);

        Credential::create([
            'user_id' => $admin->id,
            'email' => 'admin@rsajwt.local',
            'username' => 'admin',
            'phone_number' => '+1234567890',
            'password' => Hash::make('Admin@123456'),
        ]);

        $this->command->info('Admin user created: admin / Admin@123456');
    }
}
