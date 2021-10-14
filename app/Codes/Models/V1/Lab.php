<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    protected $table = 'lab';
    protected $primaryKey = 'id';
    protected $fillable = [
        'parent_id',
        'klinik_id',
        'name',
        'image',
        'desc_lab',
        'desc_benefit',
        'desc_preparation',
        'recommended_for',
        'priority'
    ];

    protected $appends = [
        'image_full',
        'price_nice'

    ];

    public function getPriceNiceAttribute()
    {
        return isset($this->price) && intval($this->price) > 0 ? number_format($this->price, 0, ',', '.') : 0;
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
        if (strlen($this->image) > 0) {
            return env('OSS_URL').'/'.$this->image;
        }
        return asset('assets/cms/images/no-img.png');
     }


}
