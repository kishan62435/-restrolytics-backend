<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('mock/orders.json');

        if (!file_exists($jsonPath)) {
            $this->command->info('Mock orders file not found. Skipping seeding.');
            return;
        }

        $orders = json_decode(file_get_contents($jsonPath), true);


        if (!is_array($orders)) {
            $this->command->info('Invalid orders data format. Skipping seeding.');
            return;
        }

        $data = array_map(function($order) {
            $startOfYear = now()->subMonths(3)->startOfDay();
            $endOfYear = now()->endOfDay();
            $randomDays = rand(0, $startOfYear->diffInDays($endOfYear));
            $randomHours = rand(0, 23);
            $randomMinutes = rand(0, 59);
            $randomSeconds = rand(0, 59);

            $randomDate = $startOfYear
                ->addDays($randomDays)
                ->addHours($randomHours)
                ->addMinutes($randomMinutes)
                ->addSeconds($randomSeconds)
                ->format('Y-m-d H:i:s');

            return array_merge($order, [
                'order_time' => $randomDate,
                'created_at' => $randomDate,
                'updated_at' => $randomDate,
            ]);
        }, $orders);

        $this->command->info('Seeding orders...');

        DB::table('orders')->insert($data);
        $this->command->info('Seeded ' . count($data) . ' orders');
    }
}
