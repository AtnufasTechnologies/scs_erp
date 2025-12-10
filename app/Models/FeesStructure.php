<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeesStructure extends Model
{
    use HasFactory;


    function program()
    {
        return $this->hasOne(MainProgram::class, 'id', 'program_id');
    }

    function batch()
    {
        return $this->hasOne(BatchMaster::class, 'id', 'batch_id');
    }

    function feepvthead()
    {
        return $this->hasMany(FeeStructureHasHead::class, 'fee_structure_id');
    }

    function feecoursemaster()
    {
        return $this->hasOne(FeeCourseMaster::class, 'id', 'course_name');
    }

    function programspivot()
    {
        return $this->hasMany(FeeStructureHasManyProgram::class, 'fee_structure_id');
    }

    // Total amount for this fee structure
    public function getTotalAmountAttribute()
    {
        return $this->feepvthead->sum('amount');
    }

    function feepayment()
    {
        return $this->hasOne(StudentPayment::class, 'fee_structure_id',);
    }

    public function feeHeads()
    {
        return $this->hasMany(FeeStructureHasHead::class, 'fee_structure_id');
    }
}
