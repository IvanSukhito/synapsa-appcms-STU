<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $table = 'doctor_schedule';
    protected $primaryKey = 'id';
    protected $fillable = [
        'doctor_id',
        'service_id',
        'weekday',
        'date_available',
        'time_start',
        'time_end',
        'type',
        'book'
    ];

    protected $appends = [
        'type_nice',
        'book_nice'
    ];

    public function getTypeNiceAttribute()
    {
        $getList = get_list_schedule_type();
        return $getList[$this->type] ?? $this->type;
    }

    public function getBookNiceAttribute()
    {
        $getList = get_list_book();
        return $getList[$this->book] ?? $this->book;
    }

    public function getDoctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

}
