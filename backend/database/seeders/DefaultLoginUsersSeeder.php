<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultLoginUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['login_id' => 'superadmin', 'employee_code' => 'SA001', 'full_name' => 'Super Admin'],
            ['login_id' => 'admin', 'employee_code' => 'TA001', 'full_name' => 'Tenant Admin'],
            ['login_id' => 'production', 'employee_code' => 'PM001', 'full_name' => 'Production Manager'],
            ['login_id' => 'store', 'employee_code' => 'SM001', 'full_name' => 'Store Manager'],
            ['login_id' => 'accounts', 'employee_code' => 'AC001', 'full_name' => 'Accountant'],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                [
                    'tenant_id' => null,
                    'login_id' => $user['login_id'],
                ],
                [
                    'branch_id' => null,
                    'password' => Hash::make('Admin@123'),
                    'employee_code' => $user['employee_code'],
                    'full_name' => $user['full_name'],
                    'mobile' => null,
                    'email' => null,
                    'role_id' => null,
                    'status' => 'Active',
                ]
            );
        }
    }
}
