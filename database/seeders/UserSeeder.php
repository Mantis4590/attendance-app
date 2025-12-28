<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'テスト 太郎',
            'email' => 'test@taro.com',
            'password' => Hash::make('test1234'),
        ]);
    }
}
