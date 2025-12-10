<?php

namespace Database\Seeders;

use App\Models\AnnualSession;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AnnualSession::insert([   
            'title' => '2023-2024',
            'status' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

         AnnualSession::insert([         
            'title' => '2024-2025',
            'status' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

         AnnualSession::insert([
            'title' => '2025-2026',
            'status' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
