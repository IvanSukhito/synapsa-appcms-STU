<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;
use App\Codes\Models\V1\DoctorCategory;

class BookNurse extends Model
{
    protected $table = 'book_nurse';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'date_booked',
        'shift_qty',
        'total',
        'status'
    ];


    protected $appends = [
        'total_nice',
    ];


    public function getTotalNiceAttribute()
    {
        return intval($this->total) > 0 ? number_format($this->total, 0, ',', '.') : 0;
    }


}
