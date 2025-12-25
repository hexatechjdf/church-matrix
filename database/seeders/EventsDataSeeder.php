<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class EventsDataSeeder extends Seeder
{
    public function run()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(600);

        $faker = Faker::create();

        $serviceNames = ['11am Worship', '9am Worship', 'Evening Service', 'Other Service'];
        $times = ['09:00:00', '11:00:00', '14:00:00', '16:00:00', '18:00:00'];
        $attendanceIds = ['regular', 'guest', 'volunteer', '305030', '313370', '313327', '5012100', '50307'];
        $values = [100, 150, 200, 250, 300, 350, 400, 450, 500];

        $batchSize = 2000;
        $totalRows = 1000000;
        $inserted = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        echo "Starting insertion of 100,000 rows...\n";

        while ($inserted < $totalRows) {
            $data = [];
            $currentBatch = min($batchSize, $totalRows - $inserted);

            for ($i = 0; $i < $currentBatch; $i++) {
                $month = $faker->numberBetween(1, 12);
                $day = $faker->numberBetween(1, 28);
                $serviceDateCarbon = Carbon::create(2025, $month, $day);
                $serviceDate = $serviceDateCarbon->format('Y-m-d');

                $weekNumber = $serviceDateCarbon->weekOfYear;
                $weekRef = '2025-' . str_pad($weekNumber, 2, '0', STR_PAD_LEFT);

                $serviceName = $faker->randomElement($serviceNames);
                $serviceTime = $faker->randomElement($times);

                $now = Carbon::now();

                $data[] = [
                    'event_time_id'       => '195245',
                    'event_id'            => '2903978',
                    'event_name'          => $serviceName,
                    'service_name'        => $serviceName,
                    'week_reference'      => $weekRef,
                    'service_date'        => $serviceDate,
                    'service_time'        => $serviceTime,
                    'headcount_id'        => null,
                    'attendance_id'       => $faker->randomElement($attendanceIds),
                    'value'               => $faker->randomElement($values),
                    'headcount_type'      => 'manual',
                    'user_id'             => 883,
                    'location_id'         => 'unknown',
                    'headcount_created_at'=> $now,
                    'headcount_updated_at'=> $now,
                    'synced_at'           => $now,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];

                $inserted++;
            }

            DB::table('events_data')->insert($data);

            echo "Inserted: $inserted / $totalRows rows (" . round(($inserted / $totalRows) * 100, 1) . "%)\n";

            unset($data);
            gc_collect_cycles();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "\nSuccessfully inserted 100,000 rows!\n";
    }
}
