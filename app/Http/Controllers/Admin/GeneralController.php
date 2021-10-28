<?php

namespace App\Http\Controllers\Admin;


use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\SubDistrict;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class GeneralController extends Controller
{
    protected $data;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->data = [
            'thisLabel' => 'General',
        ];
    }
    public function findCity(){
        $s = $this->request->get('s');
        $provinceId = intval($this->request->get('province_id'));

        $getData = City::Where('province_id', 'LIKE', strip_tags($provinceId))->get();

        return response()->json($getData);
    }

    public function findDistrict(){
        $s = $this->request->get('s');
        $cityId = intval($this->request->get('city_id'));

        $getData = District::Where('city_id', 'LIKE', strip_tags($cityId))->get();

        return response()->json($getData);
    }

    public function findSubDistrict(){
        $s = $this->request->get('s');
        $districtId = intval($this->request->get('district_id'));

        $getData = SubDistrict::Where('district_id', 'LIKE', strip_tags($districtId))->get();

        return response()->json($getData);
    }


}
