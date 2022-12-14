<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class LabSchedule extends Model
{
    protected $table = 'lab_schedule';
    protected $primaryKey = 'id';
    protected $fillable = [
        'lab_id',
        'klinik_id',
        'service_id',
        'weekday',
        'date_available',
        'time_start',
        'time_end',
        'type',
        'book'
    ];
    protected $appends = [
        'book_nice'
    ];

    public function getBookNiceAttribute()
    {
        $getList = get_list_book();
        return $getList[$this->book] ?? $this->book;
    }

    public function getLab()
    {
        return $this->belongsTo(Lab::class, 'lab_id', 'id');
    }


}
