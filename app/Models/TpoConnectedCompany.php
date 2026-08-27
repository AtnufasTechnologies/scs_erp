<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TpoConnectedCompany extends Model
{
  use HasFactory;

  protected $fillable = [
    'company_name',
    'address',
    'primary_contact_name',
    'primary_contact_phone',
    'primary_contact_email',
    'mailing_email',
    'mailing_cc',
    'mailing_bcc',
    'nature_of_business',
    'notes',
    'is_active',
    'created_by',
    'updated_by',
  ];

  protected $casts = [
    'is_active' => 'boolean',
  ];

  public function threads()
  {
    return $this->hasMany(TpoMailThread::class, 'company_id');
  }
}
