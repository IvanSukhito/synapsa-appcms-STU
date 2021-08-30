<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'district';
    protected $primaryKey = 'id';
    protected $fillable = [
        'city_id',
        'name'
    ];

    public function getCity()
    {
        return $this->belongsTo(CityCategory::class, 'city_id', 'id');
    }

    public function getSubDistrict(){
        return $this->hasMany(SubDistrict::class, 'district_id', 'id');
    }



}
