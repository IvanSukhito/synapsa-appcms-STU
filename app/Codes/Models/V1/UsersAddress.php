<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class UsersAddress extends Model
{
    protected $table = 'users_address';
    protected $primaryKey = 'id';
    protected $fillable = [
      'user_id',
      'city_id',
      'district_id',
      'sub_district_id'
      'address_name',
      'address',
      'address_detail',
      'zip_code'

    ];




}
