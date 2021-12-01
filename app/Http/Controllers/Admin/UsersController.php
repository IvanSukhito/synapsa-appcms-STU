<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\Klinik;
use Illuminate\Http\Request;

class UsersController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'klinik_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.klinik',
                'type' => 'select2',
            ],
            'city_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.city',
                'type' => 'select2',
            ],
            'district_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.district',
                'type' => 'select2',
            ],
            'sub_district_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.sub_district',
                'type' => 'select2',
            ],
            'fullname' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ]
            ],
            'address' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'textarea',
            ],
            'address_detail' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'textarea',
            ],
            'zip_code' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ]
            ],
            'dob' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'datepicker',
            ],
            'gender' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
            ],
            'nik' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'upload_ktp' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'file'
            ],
            'image' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'image'
            ],
            'phone' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'email' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'email',
            ],
            'password' => [
                'validate' => [
                    'create' => 'required',
                ],
                'list' => 0,
                'edit' => 0,
                'show' => 0,
                'type' => 'password'
            ],
            'patient' => [
                'list' => 0,
                'edit' => 0,
                'show' => 0,
            ],
            'doctor' => [
                'list' => 0,
                'edit' => 0,
                'show' => 0,
            ],
            'nurse' => [
                'list' => 0,
                'edit' => 0,
                'show' => 0,
            ],
            'verification_phone' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
            ],
            'verification_email' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
            ],
            'status' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'Aksi',
            ]
        ];

        parent::__construct(
            $request, 'general.users', 'users', 'V1\Users', 'users',
            $passingData
        );

        $getKlinik = Klinik::where('status', 1)->pluck('name', 'id')->toArray();
        $listKlinik = [0 => 'Kosong'];
        if($getKlinik) {
            foreach($getKlinik as $key => $value) {
                $listKlinik[$key] = $value;
            }
        }

        $getCity = City::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();
        $listCity = [0 => 'Kosong'];
        if($getCity) {
            foreach($getCity as $key => $value) {
                $listCity[$key] = $value;
            }
        }

        $getDistrict = District::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();
        $listDistrict = [0 => 'Kosong'];
        if($getDistrict) {
            foreach($getDistrict as $key => $value) {
                $listDistrict[$key] = $value;
            }
        }

        $getSubDistrict = SubDistrict::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();
        $listSubDistrict = [0 => 'Kosong'];
        if($getSubDistrict) {
            foreach($getSubDistrict as $key => $value) {
                $listSubDistrict[$key] = $value;
            }
        }

        $this->data['listSet']['klinik_id'] = $listKlinik;
        $this->data['listSet']['city_id'] = $listCity;
        $this->data['listSet']['district_id'] = $listDistrict;
        $this->data['listSet']['sub_district_id'] = $listKlinik;
    }
}
