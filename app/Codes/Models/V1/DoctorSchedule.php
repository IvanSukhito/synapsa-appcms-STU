<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $table = 'doctor_schedule';
    protected $primaryKey = 'id';
    protected $fillable = [
        'doctor_id',
        'date_available',
        'time_start',
        'time_end',
        'book'
    ];

    public function getDoctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }


}
