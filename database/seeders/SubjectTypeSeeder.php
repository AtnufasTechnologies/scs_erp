<?php

namespace Database\Seeders;

use App\Models\SubjectTypeMaster;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectTypeSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    SubjectTypeMaster::insert([
      'title' => 'CORE',
      'created_at' => Carbon::now(),
      'updated_at' => Carbon::now(),
    ]);
    SubjectTypeMaster::insert([
      'title' => 'MDC',
      'created_at' => Carbon::now(),
      'updated_at' => Carbon::now(),
    ]);
    SubjectTypeMaster::insert([
      'title' => 'SEC',
      'created_at' => Carbon::now(),
      'updated_at' => Carbon::now(),
    ]);
    SubjectTypeMaster::insert([
      'title' => 'AEC',
      'created_at' => Carbon::now(),
      'updated_at' => Carbon::now(),
    ]);
    SubjectTypeMaster::insert([
      'title' => 'VAC',
      'created_at' => Carbon::now(),
      'updated_at' => Carbon::now(),
    ]);
  }
}
