<?php

namespace Database\Seeders;

use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Models\AdminRolePermission;
use App\Models\AdminRoleUSer;
use App\Models\AdminUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = AdminUser::create([
            'username' => 'admin',
            'email' => 'lhqthanh1809@gmail.com',
            'password' => Hash::make('admin@123'),
            'phone' => '0935826194',
        ]);

        $permission = AdminPermission::create([
            'name' => 'all',
            'http_path' => "*"
        ]);

        $role = AdminRole::create([
            'name' => 'admin',
        ]);

        AdminRolePermission::create([
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);

        AdminRoleUSer::create([
            'role_id' => $role->id,
            'user_id' => $user->id,
        ]);
    }
}
