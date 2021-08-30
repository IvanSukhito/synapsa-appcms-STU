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

 

    
    public function getService()
    {
        return $this->belongsToMany(Service::class, 'lab_service', 'service_id', 'lab_id');
    }

    public function getLabSchedule()
    {
        return $this->hasMany(LabSchedule::class, 'lab_id', 'id');
    }
    
    public function getUploadLabImage()
    {
        return strlen($this->image) > 0 ? asset('uploads/users/'.$this->image) : asset('assets/cms/images/no-img.png');
    }


}
