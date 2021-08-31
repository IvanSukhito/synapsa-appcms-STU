<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $table = 'device_token';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'token'
    ];

    public function getUser()
    {
        return $this->belongsToMany(Users::class, 'user_device_token', 'device_token_id', 'user_id');
    }
}
