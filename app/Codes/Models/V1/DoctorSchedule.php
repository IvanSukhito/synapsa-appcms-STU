<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $table = 'doctor_schedule';
    protected $primaryKey = 'id';
    protected $fillable = [
        'doctor_id',
        'book_date',
        'book_at',
        'orders'
    ];

    public function getDoctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }
    

}
