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
        'setting_nice',
        'icon_full',
    ];

    public function getShippingPriceAttribute()
    {
        return $this->price;
    }

    public function getShippingPriceNiceAttribute()
    {
        return $this->price > 0 ? number_format($this->price, 0, ',', '.') : 0;
    }

    public function getSettingNiceAttribute()
    {
        return json_decode($this->settings, TRUE);
    }

    public function getIconFullAttribute()
    {
        if (strlen($this->icon) > 0) {
            return env('OSS_URL').'/'.$this->icon;
        }
        return asset('assets/cms/images/no-img.png');
    }


}
