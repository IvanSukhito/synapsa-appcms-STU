<?php

namespace App\Http\Controllers\Admin;


use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Product;
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
    public function findProductSynapsa(){
        $category = intval($this->request->get('category'));

        if($category){
            $getProduct = Product::Where('product_category_id', 'LIKE', strip_tags($category))->where('klinik_id', 0)->get();
        }


        return response()->json($getProduct);
    }

    public function appointmentLabSchedule(){

        $clinic =  session()->get('admin_clinic_id');
        $getData = AppointmentLab::selectRaw('appointment_lab.*, transaction_details.lab_name as lab_name')
                    ->leftJoin('transaction_details', 'transaction_details.transaction_id','=','appointment_lab.transaction_id')
                    ->where('klinik_id', $clinic)->get();

        $dataArr = array();
        foreach ($getData as $list){
            $dataArr[] = array(
                'id' => $list->id,
                'lab_name' => $list->lab_name,
                'status' => $list->status,
                'code' => $list->code,
                'patient' => $list->patient_name,
                'time_start' => $list->date.' '.$list->time_start,
                'time_end' => $list->date.' '.$list->time_end,
            );
        }

        return response()->json($dataArr);
    }


}
