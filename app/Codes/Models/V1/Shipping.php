<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    protected $table = 'shipping';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'price',
        'icon',
        'orders',
        'settings',
        'status'
    ];

    protected $appends = [
        'shipping_price',
        'shipping_price_nice',
        'icon_full',
    ];

    public function getShippingPriceAttribute()
    {
        return 15000;
    }

    public function getShippingPriceNiceAttribute()
    {
        return number_format(15000, 0, ',', '.');
    }

    public function getIconFullAttribute()
    {
        if (strlen($this->icon) > 0) {
            return env('OSS_URL').'/'.$this->icon;
        }
        return asset('assets/cms/images/no-img.png');
    }


}
