<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentDocumentMasterSeeder extends Seeder
{
  public function run(): void
  {
    if (!Schema::hasTable('student_document_masters')) {
      return;
    }

    $now = now();
    $rows = [
      ['name' => 'Resume', 'slug' => 'resume', 'is_resume' => 1, 'is_active' => 1, 'sort_order' => 1],
      ['name' => 'Aadhaar Card', 'slug' => 'aadhaar_card', 'is_resume' => 0, 'is_active' => 1, 'sort_order' => 2],
      ['name' => 'PAN Card', 'slug' => 'pan_card', 'is_resume' => 0, 'is_active' => 1, 'sort_order' => 3],
      ['name' => 'Marksheet', 'slug' => 'marksheet', 'is_resume' => 0, 'is_active' => 1, 'sort_order' => 4],
      ['name' => 'Portfolio', 'slug' => 'portfolio', 'is_resume' => 0, 'is_active' => 1, 'sort_order' => 5],
      ['name' => 'Cover Letter', 'slug' => 'cover_letter', 'is_resume' => 0, 'is_active' => 1, 'sort_order' => 6],
      ['name' => 'Passport Photo', 'slug' => 'passport_photo', 'is_resume' => 0, 'is_active' => 1, 'sort_order' => 7],
      ['name' => 'College ID Card', 'slug' => 'identity_card', 'is_resume' => 0, 'is_active' => 1, 'sort_order' => 8],
      ['name' => 'NOC', 'slug' => 'noc', 'is_resume' => 0, 'is_active' => 1, 'sort_order' => 9],
    ];

    $payload = collect($rows)
      ->map(function ($row) use ($now) {
        return [
          'name' => $row['name'],
          'slug' => $row['slug'],
          'is_resume' => $row['is_resume'],
          'is_active' => $row['is_active'],
          'sort_order' => $row['sort_order'],
          'created_at' => $now,
          'updated_at' => $now,
        ];
      })
      ->all();

    DB::table('student_document_masters')->upsert(
      $payload,
      ['slug'],
      ['name', 'is_resume', 'is_active', 'sort_order', 'updated_at']
    );
  }
}
