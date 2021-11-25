<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class UsersAddress extends Model
{
    protected $table = 'users_address';
    protected $primaryKey = 'id';
    protected $fillable = [
      'user_id',
      'province_id',
      'city_id',
      'district_id',
      'sub_district_id',
      'address_name',
      'address',
      'address_detail',
      'zip_code'
    ];

    protected $appends = [
        'receiver',
        'province',
        'city',
        'district',
        'sub_district',
        'province_name',
        'city_name',
        'district_name',
        'sub_district_name'
    ];

    public function getReceiverAttribute()
    {
        return $this->address_name;
    }

    public function getProvinceAttribute()
    {
        return $this->province_id;
    }

    public function getCityAttribute()
    {
        return $this->city_id;
    }

    public function getDistrictAttribute()
    {
        return $this->district_id;
    }

    public function getSubDistrictAttribute()
    {
        return $this->sub_district_id;
    }

    public function getProvinceNameAttribute()
    {
        $getData = $this->getProvince()->first();
        if ($getData) {
            return $getData->name;
        }
        return '';
    }

    public function getCityNameAttribute()
    {
        $getData = $this->getCity()->first();
        if ($getData) {
            return $getData->name;
        }
        return '';
    }

    public function getDistrictNameAttribute()
    {
        $getData = $this->getDistrict()->first();
        if ($getData) {
            return $getData->name;
        }
        return '';
    }

    public function getSubDistrictNameAttribute()
    {
        $getData = $this->getSubDistrict()->first();
        if ($getData) {
            return $getData->name;
        }
        return '';
    }

    public function getProvince()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    public function getCity()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function getDistrict()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function getSubDistrict()
    {
        return $this->belongsTo(SubDistrict::class, 'sub_district_id', 'id');
    }

}
