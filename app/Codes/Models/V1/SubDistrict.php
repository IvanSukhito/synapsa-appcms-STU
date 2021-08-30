<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class SubDistrict extends Model
{
    protected $table = 'sub_district';
    protected $primaryKey = 'id';
    protected $fillable = [
        'district_id',
        'name',

    ];

    public function getDistricy()
    {
        return $this->belongsTo(DistrictCategory::class, 'district_id', 'id');
    }




}
