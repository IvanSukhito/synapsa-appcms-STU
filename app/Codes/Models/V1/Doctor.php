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
        'formal_edu',
        'nonformal_edu',
    ];

    protected $appends = [
        'status_nice',
        'gender_nice',
        'image_full',
        'price_nice'
    ];

    public function getStatusNiceAttribute()
    {
        if (isset($this->status)) {
            $getList = get_list_active_inactive();
            return $getList[$this->status] ?? $this->status;
        }
        return '';
    }

    public function getGenderNiceAttribute()
    {
        if (isset($this->gender)) {
            $getList = get_list_gender();
            return $getList[$this->gender] ?? $this->gender;
        }
        return '';
    }

    public function getImageFullAttribute()
    {
        if (isset($this->image)) {
            if (strlen($this->image) > 0) {
                return env('OSS_URL').'/'.$this->image;
            }
        }
        return asset('assets/cms/images/no-img.png');
    }

    public function getPriceNiceAttribute()
    {
        return isset($this->price) && intval($this->price) > 0 ? number_format($this->price, 0, ',', '.') : 0;
    }

    public function getCategory()
    {
        return $this->belongsTo(DoctorCategory::class, 'doctor_category_id', 'id');
    }


    public function getService()
    {
        return $this->belongsToMany(Service::class, 'doctor_service', 'doctor_id', 'service_id');
    }

    public function getDoctorSchedule()
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id', 'id');
    }



}
