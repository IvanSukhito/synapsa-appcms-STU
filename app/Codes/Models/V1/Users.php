<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Users extends Model implements JWTSubject
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'klinik_id',
        'city_id',
        'district_id',
        'sub_district_id',
        'fullname',
        'address',
        'address_detail',
        'zip_code',
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

    protected $hidden = ['password'];

    protected $appends = [
        'upload_ktp_full',
        'status_nice'
    ];

    public function getUploadKtpFullAttribute()
    {
        return strlen($this->upload_ktp) > 0 ? asset('uploads/users/'.$this->upload_ktp) : asset('assets/cms/images/no-img.png');
    }

    public function getStatusNiceAttribute()
    {
        $getList = get_list_active_inactive();
        return $getList[$this->status] ?? $this->status;
    }

    public function getUploadImage()
    {
        return strlen($this->image) > 0 ? asset('uploads/users/'.$this->image) : asset('assets/cms/images/user-default.png');
    }

    public function getDeviceToken()
    {
        return $this->belongsToMany(DeviceToken::class, 'user_device_token', 'user_id', 'device_token_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

}
