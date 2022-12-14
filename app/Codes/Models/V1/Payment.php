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
        'service',
        'type_payment',
        'settings',
        'type',
        'status'
    ];

    protected $appends = [
        'icon_img_full',
        'price_nice',
        'setting_data',
    ];

    public function getIconImgFullAttribute()
    {
        if (strlen($this->icon_img) > 0) {
            return env('OSS_URL').'/'.$this->icon_img;
        }
        return asset('assets/cms/images/no-img.png');
    }

    public function getPriceNiceAttribute()
    {
        return isset($this->price) && intval($this->price) > 0 ? number_format($this->price, 0, ',', '.') : 0;
    }

    public function getSettingDataAttribute()
    {
        $getData = json_decode($this->settings, true);
        if ($getData) {
            return $getData;
        }
        return [];
    }

}
