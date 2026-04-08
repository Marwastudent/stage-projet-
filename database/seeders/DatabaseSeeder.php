<?php

namespace Database\Seeders;

use App\Models\SportsHall;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        SportsHall::updateOrCreate(
            ['matricule' => 'HALL-CASA-001'],
            [
                'name' => 'Atlas Fitness Casa',
                'localisation' => 'Casablanca - Maarif',
            ]
        );

        SportsHall::updateOrCreate(
            ['matricule' => 'HALL-RABAT-002'],
            [
                'name' => 'Rabat Sport Center',
                'localisation' => 'Rabat - Agdal',
            ]
        );

        User::updateOrCreate(
            ['email' => 'marwaaitbahadou4@gmail.com'],
            [
                'name' => 'Admin',
                'password' => 'password123',
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'aitbahadoumarwa16@gmail.com'],
            [
                'name' => 'Manager',
                'password' => 'Manager@2026',
                'role' => 'manager',
            ]
        );

        User::updateOrCreate(
            ['email' => 'client@club.local'],
            [
                'name' => 'Client',
                'password' => 'password123',
                'role' => 'client',
            ]
        );
    }
}
