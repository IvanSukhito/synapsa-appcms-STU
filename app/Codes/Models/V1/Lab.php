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
        'image',
        'desc_lab',
        'desc_benefit',
        'desc_preparation',
        'recommended_for'
    ];

    protected $appends = [
        'image_full',
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
    
    public function getImageFullAttribute()
    {
       // return asset('assets/cms/images/no-img.png');
        return strlen($this->image) > 0 ? asset('uploads/lab/'.$this->image) : asset('assets/cms/images/no-img.png');
    }


}
