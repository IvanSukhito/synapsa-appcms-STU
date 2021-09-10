<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;
use App\Codes\Models\V1\DoctorCategory;

class Doctor extends Model
{
    protected $table = 'doctor';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'doctor_category_id',
        'price',
        'formal_edu',
        'nonformal_edu',
    ];

    protected $appends = [
        'price_nice'
    ];

    public function getPriceNiceAttribute()
    {
        return intval($this->price) > 0 ? number_format($this->price, 0) : 0;
    }

    public function getCategory()
    {
        return $this->belongsTo(DoctorCategory::class, 'doctor_category_id', 'id');
    }


    public function getService()
    {
        return $this->belongsToMany(Service::class, 'doctor_service', 'service_id', 'doctor_id');
    }

    public function getDoctorSchedule()
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id', 'id');
    }



}
