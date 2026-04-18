<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuarios de prueba
        User::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Diana Prince',
            'email' => 'diana@example.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Eve Wilson',
            'email' => 'eve@example.com',
            'password' => Hash::make('password123'),
        ]);

        // También puedes usar factory para generar más usuarios
        User::factory(10)->create();
    }
}
