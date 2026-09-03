<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternationalOfficeInstitution extends Model
{
  use HasFactory;

  protected $fillable = [
    'institution_name',
    'contact_person',
    'contact_number',
    'email',
    'address',
    'has_mou',
    'mou_document_path',
    'remarks',
    'created_by_user_id',
  ];

  protected $casts = [
    'has_mou' => 'boolean',
  ];
}
