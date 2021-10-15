<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'city';
    protected $primaryKey = 'id';
    protected $fillable = [
      'province_id',
      'name',
    ];

    public function getDistrict(){
        return $this->hasMany(District::class, 'city_id', 'id');
    }




}
