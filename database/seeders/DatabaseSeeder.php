<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // $this->call(PopularPlacesSeeder::class);
        // $this->call([PlanSeeder::class]);
        // $this->call([BookingAddonsSeeder::class]);
        // $this->call([VehicleCategorySeeder::class]);
        $this->call(AdminUserSeeder::class);
    }
}