<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Sliders extends Model
{
    protected $table = 'sliders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'banner_category_id',
        'klinik_id',
        'title',
        'image',
        'target',
        'time_start',
        'time_end',
        'orders',
        'status',
    ];

    protected $appends = [
        'image_full'
    ];


    public function getImageFullAttribute()
    {
        if (strlen($this->image) > 0) {
            return env('OSS_URL').'/'.$this->image;
        }
        return asset('assets/cms/images/no-img.png');
    }


}
