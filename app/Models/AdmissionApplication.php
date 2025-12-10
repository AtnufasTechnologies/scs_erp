<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AdmissionApplication extends Model
{
    use HasFactory;

    public function registration()
    {
        return $this->hasOne(AdmissionRegistration::class, 'id', 'reg_id');
    }
    // public function dept()
    // {
    //     return $this->hasOne(Department::class, 'id', 'dept_id');
    // }
    // public function course()
    // {
    //     return $this->hasOne(CourseCombination::class, 'id', 'course_id');
    // }
    public function religionInfo()
    {
        return $this->hasOne(ReligionMaster::class, 'id', 'religion_id');
    }
    public function paymentInfo()
    {
        return $this->hasOne(AdmissionApplicationPaymentLog::class, 'user_id', 'user_id');
    }
    protected $table = 'admission_applications';

    protected $appends = ['pic_url', 'adhaar_url', 'certificate_x', 'certificate_xii', 'UgDoc'];

    /**
     * Return full Wasabi URL of the profile picture.
     */
    public function getPicUrlAttribute()
    {
        if ($this->pic) {
            return Storage::disk('s3')->url('profile/' . $this->pic);
        }
        return null; // fallback if needed
    }

    public function getAdhaarUrlAttribute()
    {
        if ($this->pic) {
            return Storage::disk('s3')->url('adhaar/' . $this->adhaar);
        }
        return null; // fallback if needed
    }

    public function getCertificateXAttribute()
    {
        if ($this->pic) {
            return Storage::disk('s3')->url('certificate10/' . $this->certificate_10);
        }
        return null; // fallback if needed
    }

    public function getCertificateXiiAttribute()
    {
        if ($this->pic) {
            return Storage::disk('s3')->url('certificate12/' . $this->certificate_12);
        }
        return null; // fallback if needed
    }

    public function getUgDocAttribute()
    {
        if ($this->pic) {
            return Storage::disk('s3')->url('pgdoc/' . $this->lastinstdoc);
        }
        return null; // fallback if needed
    }
}
