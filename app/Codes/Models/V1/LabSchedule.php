<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class LabSchedule extends Model
{
    protected $table = 'lab_schedule';
    protected $primaryKey = 'id';
    protected $fillable = [
        'lab_id',
        'book_date',
        'book_at',
        'orders'
    ];

    public function getLab()
    {
        return $this->belongsTo(Lab::class, 'lab_id', 'id');
    }
    

}
