<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        $users = [
                [
                    'name' => 'jake',
                    'email' => 'jake@yopmail.com',
                    'password' => Hash::make('12345678'),
                ],
                [
                    'name' => 'jhon',
                    'email' => 'jhon@yopmail.com',
                    'password' => Hash::make('12345678'),
                ],
                [
                    'name' => 'Ben',
                    'email' => 'Ben@yopmail.com',
                    'password' => Hash::make('12345678'),
                ]
            ];

        foreach($users as $user):
            User::create($user);
        endforeach;
    }
}
