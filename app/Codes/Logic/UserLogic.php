<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\DeviceToken;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersAddress;

class UserLogic
{
    public function __construct()
    {
    }

    public function userCreatePatient($saveData)
    {
        $getUser = Users::create([
            'klinik_id' => intval($saveData['klinik_id']) ?? '',
            'province_id' => intval($saveData['province_id']) ?? '',
            'city_id' => intval($saveData['city_id']) ?? '',
            'district_id' => intval($saveData['district_id']) ?? '',
            'sub_district_id' => intval($saveData['sub_district_id']) ?? '',
            'fullname' => $saveData['fullname'] ?? '',
            'address' => $saveData['address'] ?? '',
            'address_detail' => $saveData['address_detail'] ?? '',
            'zip_code' => $saveData['zip_code'] ?? '',
            'dob' => $saveData['dob'] ?? '',
            'gender' => $saveData['gender'] ?? '',
            'nik' => $saveData['nik'] ?? '',
            'phone' => $saveData['phone'] ?? '',
            'email' => $saveData['email'] ?? '',
            'password' => bcrypt($saveData['password']),
            'status' => intval($saveData['status']) ?? 80,
            'patient' => 1,
            'upload_ktp' => $saveData['upload_ktp'],
            'image' => $saveData['image'],
        ]);

        UsersAddress::create([
            'user_id' => $getUser->id,
            'province_id' => intval($saveData['province_id']) ?? '',
            'city_id' => intval($saveData['city_id']) ?? '',
            'district_id' => intval($saveData['district_id']) ?? '',
            'sub_district_id' => intval($saveData['sub_district_id']) ?? '',
            'zip_code' => $saveData['zip_code'] ?? '',
            'address_name' => $saveData['fullname'] ?? '',
            'address' => $saveData['address'] ?? '',
            'address_detail' => $saveData['address_detail'] ?? ''
        ]);

        return $getUser;

    }

    public function userUpdatePatient($userId, $saveData)
    {
        $getUser = Users::where('id', $userId)->first();
        if (!$getUser) {
            return false;
        }

        foreach ($saveData as $key => $val) {
            if ($key == 'password') {
                $getUser->$key = bcrypt($val);
            }
            else {
                $getUser->$key = $val;
            }
        }

        $getUser->save();

        return $getUser;

    }

    public function userUpdateAddressPatient($userId, $saveData)
    {
        $getUsersAddress = UsersAddress::firstOrCreate([
            'user_id' => $userId
        ]);
        if (!$getUsersAddress) {
            return false;
        }

        foreach ($saveData as $key => $val) {
            $getUsersAddress->$key = $val;
        }

        $getUsersAddress->save();

        return $getUsersAddress;

    }

    public function updateToken($userId, $getToken)
    {
        $getDeviceToken = DeviceToken::firstOrCreate([
            'token' => $getToken
        ]);
        $getDeviceToken->getUser()->sync([$userId]);
    }

    public function userInfo($userId)
    {
        $getUser = Users::where('id', $userId)->first();
        if (!$getUser) {
            return false;
        }
        return $getUser;
    }

    public function userAddress($userId)
    {
        $getUserAddress = UsersAddress::where('user_id', $userId)->first();
        if (!$getUserAddress) {
            return false;
        }
        return $getUserAddress;
    }

    public function historyTransaction($userId)
    {

    }

    public function scheduleAll($userId)
    {

    }

}
