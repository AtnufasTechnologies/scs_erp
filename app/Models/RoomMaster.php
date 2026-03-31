<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomMaster extends Model
{
    use HasFactory;

    protected $table = 'room_masters';

    protected $fillable = [
        'room_name',
        'room_code',
        'block_id',
        'floor',
        'capacity',
        'room_type',
        'status',
    ];

    public function block()
    {
        return $this->belongsTo(AcademicBlock::class, 'block_id');
    }

    public function seatingAllocations()
    {
        return $this->hasMany(\App\Models\ExamSystem\SeatingAllocation::class, 'room_id');
    }
}
