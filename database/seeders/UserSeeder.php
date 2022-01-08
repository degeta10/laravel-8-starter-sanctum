<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'qwe123',
            'remember_token' => Str::random(10),
        ]);
        \App\Models\User::factory(10)->create();
    }
}
