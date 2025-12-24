<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ChurchRecordsTableSeeder extends Seeder
{
    public function run()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('church_records')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $faker = \Faker\Factory::create();

        $campusIds   = [137882, 137883, 137884, 137885];
        $categoryIds = [9, 10, 11, 12, 13, 14];

        $startDate = Carbon::parse('2023-01-01');

        $totalRecords = 1_000_000;
        $batchSize    = 2000;
        $inserted     = 0;

        echo "Seeding 1,000,000 church_records...\n";

        while ($inserted < $totalRecords) {
            $records = [];

            for ($i = 0; $i < $batchSize && $inserted < $totalRecords; $i++) {

                $weekOffset = rand(0, 104); 
                $weekStart  = $startDate->copy()->addWeeks($weekOffset)->startOfWeek(Carbon::SUNDAY);

                $campusId   = $campusIds[array_rand($campusIds)];
                $categoryId = $categoryIds[array_rand($categoryIds)];

                $value = match ($categoryId) {
                    9  => rand(120, 420),
                    10 => rand(3, 22),
                    11 => rand(80_000, 350_000),
                    12 => rand(8, 55),
                    13 => rand(2, 12),
                    14 => rand(15, 120),
                    default => rand(50, 300),
                };

                $records[] = [
                    'record_unique_id'       => (string) Str::uuid(),
                    'organization_unique_id' => 1,
                    'category_unique_id'     => $categoryId,
                    'week_reference'         => (int)($weekStart->format('y') . str_pad($weekStart->weekOfYear, 2, '0', STR_PAD_LEFT)),
                    'week_no'                => $weekStart->weekOfYear,
                    'week_volume'            => $weekStart->format('M d') . ' - ' . $weekStart->copy()->endOfWeek()->format('M d, Y'),
                    'service_date_time'      => $weekStart->format('Y-m-d 10:30:00'),
                    'service_time'           => ['09:00 AM','10:30 AM','06:00 PM','07:30 PM'][array_rand([0,1,2,3])],
                    'service_timezone'       => 'Pakistan Standard Time',
                    'value'                  => $value,
                    'service_unique_time_id' => rand(1000, 9999),
                    'event_unique_id'        => rand(0,1) ? 3834 : 3835,
                    'campus_unique_id'       => $campusId,
                    'record_created_at'      => now(),
                    'record_updated_at'      => now(),
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ];

                $inserted++;
            }

            DB::table('church_records')->insert($records);

            echo "Inserted: {$inserted} / {$totalRecords}\n";
        }

        echo "DONE! 1,000,000 records inserted successfully.\n";
    }
}
