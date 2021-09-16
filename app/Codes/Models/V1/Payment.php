<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'icon_img',
        'orders',
        'settings',
        'type',
        'status'
    ];

    protected $appends = [
        'icon_full',
        'price_nice',
    ];

    public function getImageFullAttribute()
    {
        return asset('assets/cms/images/no-img.png');
        if (strlen($this->icon) > 0) {
            return env('OSS_URL').'/'.$this->icon;
        }
        return asset('assets/cms/images/no-img.png');
    }

    public function getPriceNiceAttribute()
    {
        return '15.000';
        return intval($this->price) > 0 ? number_format($this->price, 0, '.', '.') : 0;
    }

}
