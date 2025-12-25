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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('church_records')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $faker = \Faker\Factory::create();

        $campusIds = [137882, 137883, 137884, 137885];
        $categoryIds = [9, 10, 11, 12, 13, 14];

        $startDate = Carbon::parse('2023-01-01');

        $records = [];
        $counter = 0;
        $totalRecords = 100;

        echo "Generating exactly 100 records...\n";

        while ($counter < $totalRecords) {
            $currentWeekStart = $startDate->copy()->addWeeks($counter % 10)->startOfWeek(Carbon::SUNDAY);

            foreach ($campusIds as $campusId) {
                foreach ($categoryIds as $categoryId) {
                    if ($counter >= $totalRecords) {
                        break 3;
                    }

                    $eventId = $faker->optional(0.5)->randomElement([3834, 3835]);

                    $value = match($categoryId) {
                        9  => $faker->numberBetween(120, 420),
                        10 => $faker->numberBetween(3, 22),
                        11 => $faker->randomFloat(2, 80000, 350000),
                        12 => $faker->numberBetween(8, 55),
                        13 => $faker->numberBetween(2, 12),
                        14 => $faker->numberBetween(15, 120),
                        default => $faker->numberBetween(50, 300)
                    };

                    $weekRef = $currentWeekStart->format('y') . str_pad($currentWeekStart->weekOfYear, 2, '0', STR_PAD_LEFT);

                    $records[] = [
                        'record_unique_id'       => (string) Str::uuid(),
                        'organization_unique_id' => 1,
                        'category_unique_id'     => $categoryId,
                        'week_reference'         => (int)$weekRef,
                        'week_no'                => $currentWeekStart->weekOfYear,
                        'week_volume'            => $currentWeekStart->format('M d') . ' - ' . $currentWeekStart->copy()->endOfWeek()->format('M d, Y'),
                        'service_date_time'      => $currentWeekStart->format('Y-m-d 10:30:00'),
                        'service_time'           => $faker->randomElement(['09:00 AM', '10:30 AM', '06:00 PM', '07:30 PM']),
                        'service_timezone'       => 'Pakistan Standard Time',
                        'value'                  => $value,
                        'service_unique_time_id' => $faker->numberBetween(1000, 9999),
                        'event_unique_id'        => $eventId,
                        'campus_unique_id'       => $campusId,
                        'record_created_at'      => now()->format('Y-m-d H:i:s'),
                        'record_updated_at'      => now()->format('Y-m-d H:i:s'),
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ];

                    $counter++;
                }
            }
        }

        // Insert all records
        DB::table('church_records')->insert($records);

        echo "Done! Total records inserted: $counter\n";
    }
}
