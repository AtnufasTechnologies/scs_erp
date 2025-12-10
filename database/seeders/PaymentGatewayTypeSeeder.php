<?php

namespace Database\Seeders;

use App\Models\PaymentGatewayType;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentGatewayTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        PaymentGatewayType::insert([
            'title' => 'easebuzz',
            'created_at' => $now,
        ]);
        PaymentGatewayType::insert([
            'title' => 'billdesk',
            'created_at' => $now,
        ]);
    }
}
