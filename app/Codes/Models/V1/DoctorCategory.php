<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class DoctorCategory extends Model
{
    protected $table = 'doctor_category';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'icon_img',
        'orders'
    ];

    protected $appends = [
        'upload_icon_image'
    ];


    public function getDoctor()
    {
        return $this->hasMany(Doctor::class, 'doctor_category_id', 'id');
    }

    public function getUploadIconImageAttribute()
    {
        return strlen($this->icon_img) > 0 ? asset('uploads/users/'.$this->icon_img) : asset('assets/cms/images/no-img.png');
    }

}
