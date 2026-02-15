<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HrUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'HR@gmail.com'],
            [
                'name' => 'HR',
                'password' => Hash::make('HR12345678'),
                'role' => 'hr',
            ]
        );
    }
}
