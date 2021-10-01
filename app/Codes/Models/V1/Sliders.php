<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Sliders extends Model
{
    protected $table = 'sliders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
        'image',
        'target',
        'orders',
        'status'
    ];

    protected $appends = [
        'image_full'
    ];


    public function getImageFullAttribute()
    {
        return asset('assets/cms/images/no-img.png');
//        return strlen($this->image) > 0 ? asset('synapsaapps/sliders/'.$this->image) : asset('assets/cms/images/no-img.png');
    }



}
