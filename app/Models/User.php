<?php

namespace App\Models;

use App\Codes\Models\V1\DeviceToken;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'klinik_id',
        'city_id',
        'district_id',
        'sub_district_id',
        'interest_service_id',
        'interest_category_id',
        'fullname',
        'address',
        'address_detail',
        'zip_code',
        'pob',
        'dob',
        'gender',
        'nik',
        'upload_ktp',
        'image',
        'phone',
        'email',
        'password',
        'patient',
        'doctor',
        'nurse',
        'verification_phone',
        'verification_email'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getDeviceToken()
    {
        return $this->belongsToMany(DeviceToken::class, 'user_device_token', 'user_id', 'device_token_id');
    }
}
