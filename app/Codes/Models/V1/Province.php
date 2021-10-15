<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'province';
    protected $primaryKey = 'id';
    protected $fillable = [
      'name'
    ];

    public function getCity(){
        return $this->hasMany(City::class, 'province_id', 'id');
    }




}
