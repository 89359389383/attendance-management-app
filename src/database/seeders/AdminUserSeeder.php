<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = 'p!N3x55$uM2y#Ft9';
        $userPassword = 'Kz8#rTq55@LmWv4z';

        // 管理者ユーザー作成
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'AdminUser',
                'password' => Hash::make($adminPassword),
                'is_admin' => true,
            ]
        );

        // 固定の一般ユーザー作成
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'TestUser',
                'password' => Hash::make($userPassword),
                'is_admin' => false,
            ]
        );
    }
}
