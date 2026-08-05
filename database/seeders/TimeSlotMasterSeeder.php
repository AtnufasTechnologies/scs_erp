<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeSlotMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    { {
            DB::table('hour_masters')->truncate();

            $slots = [];

            /*
        |--------------------------------------------------------------------------
        | Morning Shift
        | Shift ID = 1
        | Starts : 07:10 AM
        | 8 Slots × 50 Minutes
        |--------------------------------------------------------------------------
        */

            $start = Carbon::createFromTime(7, 10);

            for ($i = 1; $i <= 8; $i++) {

                $end = $start->copy()->addMinutes(50);

                $slots[] = [
                    'shift_id'     => 1,
                    'hour_no'      => $i,
                    'name'    => 'Hour ' . $i,
                    'start_time'   => $start->format('H:i:s'),
                    'end_time'     => $end->format('H:i:s'),
                    'is_teaching'  => $i <= 6 ? 1 : 0,
                    'status'       => 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                $start = $end;
            }

            /*
        |--------------------------------------------------------------------------
        | Day Shift
        | Shift ID = 2
        | Starts : 08:00 AM
        |--------------------------------------------------------------------------
        */

            $start = Carbon::createFromTime(8, 0);

            for ($i = 1; $i <= 8; $i++) {

                $end = $start->copy()->addMinutes(50);

                $slots[] = [
                    'shift_id'     => 2,
                    'hour_no'      => $i,
                    'name'    => 'Hour ' . $i,
                    'start_time'   => $start->format('H:i:s'),
                    'end_time'     => $end->format('H:i:s'),
                    'is_teaching'  => $i <= 6 ? 1 : 0,
                    'status'       => 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                $start = $end;
            }

            /*
        |--------------------------------------------------------------------------
        | Common Shift
        | Shift ID = 3
        |--------------------------------------------------------------------------
        */

            $start = Carbon::createFromTime(8, 0);

            for ($i = 1; $i <= 8; $i++) {

                $end = $start->copy()->addMinutes(50);

                $slots[] = [
                    'shift_id'     => 3,
                    'hour_no'      => $i,
                    'name'    => 'Hour ' . $i,
                    'start_time'   => $start->format('H:i:s'),
                    'end_time'     => $end->format('H:i:s'),
                    'is_teaching'  => $i <= 6 ? 1 : 0,
                    'status'       => 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                $start = $end;
            }

            DB::table('hour_masters')->insert($slots);
        }
    }
}
