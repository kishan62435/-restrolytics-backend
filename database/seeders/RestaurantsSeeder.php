<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestaurantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('mock/restaurants.json');

        if(!file_exists($jsonPath)) {
            $this->command->info('Mock restaurants file not found. Skipping seeding.');
            return;
        }

        $restaurants = json_decode(file_get_contents($jsonPath), true);

        if(!is_array($restaurants)) {
            $this->command->info('Invalid restaurants data format. Skipping seeding.');
            return;
        }

        $this->command->info('Seeding restaurants...');

        $data = array_map(function($restaurant) {
            return array_merge($restaurant, [
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }, $restaurants);

        DB::table('restaurants')->insert($data);
        $this->command->info('Seeded ' . count($data) . ' restaurants');
    }
}
