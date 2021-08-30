<?php

namespace App\Codes\Models\V1;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
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
        'phone',
        'email',
        'password',
        'patient',
        'doctor',
        'nurse',
        'verification_phone',
        'verification_email'
    ];

    protected $appends = [
        'upload_ktp_full'
    ];

    public function getUploadKtpAttribute()
    {
        return strlen($this->image) > 0 ? asset('uploads/users/'.$this->image) : asset('assets/html/images/register.svg');
    }

}
