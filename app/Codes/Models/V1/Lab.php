<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    protected $table = 'lab';
    protected $primaryKey = 'id';
    protected $fillable = [
        'parent_id',
        'name',
        'price',
        'thumbnail_img',
        'image',
        'desc_lab',
        'desc_benefit',
        'desc_preparation',
        'recommended_for'
    ];

    protected $appends = [
        'upload_lab_image',
        'price_nice'
    ];

    public function getPriceNiceAttribute()
    {
        return intval($this->price) > 0 ? number_format($this->price, 0) : 0;
    }

    
    public function getService()
    {
        return $this->belongsToMany(Service::class, 'lab_service', 'service_id', 'lab_id');
    }

    public function getLabSchedule()
    {
        return $this->hasMany(LabSchedule::class, 'lab_id', 'id');
    }
    
    public function getUploadLabImageAttribute()
    {
        return strlen($this->thumbnail_img) > 0 ? asset('uploads/lab/'.$this->thumbnail_img) : asset('assets/cms/images/no-img.png');
    }


}
