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

    protected $hidden = ['password'];

    protected $appends = [
        'upload_ktp_full',
        'status_nice',
        'gender_nice',
        'image_full',
        'price_nice'
    ];

    public function getUploadKtpFullAttribute()
    {
        if (strlen($this->upload_ktp) > 0) {
            return env('OSS_URL').'/'.$this->upload_ktp;
        }
        return asset('assets/cms/images/no-img.png');
    }

    public function getStatusNiceAttribute()
    {
        $getList = get_list_active_inactive();
        return $getList[$this->status] ?? $this->status;
    }

    public function getGenderNiceAttribute()
    {
        $getList = get_list_gender();
        return $getList[$this->gender] ?? $this->gender;
    }

    public function getImageFullAttribute()
    {
        if (strlen($this->image) > 0) {
            return env('OSS_URL').'/'.$this->image;
        }
        return asset('assets/cms/images/no-img.png');
    }

    public function getPriceNiceAttribute()
    {
        return isset($this->price) && intval($this->price) > 0 ? number_format($this->price, 0, ',', '.') : 0;
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
