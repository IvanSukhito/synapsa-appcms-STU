<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class DoctorCategory extends Model
{
    protected $table = 'doctor_category';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'icon_img'
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
        return strlen($this->image) > 0 ? asset('uploads/users/'.$this->image) : asset('assets/cms/images/no-img.png');
    }

}
