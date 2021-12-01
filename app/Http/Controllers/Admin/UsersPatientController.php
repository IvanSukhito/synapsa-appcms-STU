<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\Province;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\Klinik;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class UsersPatientController extends _CrudController
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
                'type' => 'texteditor',
                'list' => 0,
                'create' => 0,
                'show' => 0,
                'edit' => 0,
            ],
            'address_detail' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'texteditor',
                'list' => 0,
                'create' => 0,
                'show' => 0,
                'edit' => 0,
            ],
            'zip_code' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'create' => 0,
            ],
            'dob' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'datepicker',
                'list' => 0,
            ],
            'gender' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
                'list' => 0,
            ],
            'nik' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
            ],
            'upload_ktp_full' => [
                'validate' => [
                    'create' => 'required',
                ],
                'type' => 'image',
                'lang' => 'ktp',
                'list' => 0,
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
            $request, 'general.users_patient', 'users-patient', 'V1\Users', 'users-patient',
            $passingData
        );

        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.users.patient.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.users.patient.forms';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.users.patient.forms_show';
        $this->listView['dataTable'] = env('ADMIN_TEMPLATE').'.page.users.patient.list_button';
        $this->listView['forgotPassword'] = env('ADMIN_TEMPLATE').'.page.users.patient.forms_password';

        $getKlinik = Klinik::where('status', 80)->pluck('name', 'id')->toArray();
        if($getKlinik) {
            foreach($getKlinik as $key => $value) {
                $listKlinik[$key] = $value;
            }
        }

        $getProvince = Province::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();
        $listProvince = [0 => 'Kosong'];
        if($getProvince) {
            foreach($getProvince as $key => $value) {
                $listProvince[$key] = $value;
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
        if($getSubDistrict) {
            foreach($getSubDistrict as $key => $value) {
                $listSubDistrict[$key] = $value;
            }
        }

        $this->data['listSet']['klinik_id'] = $listKlinik;
        $this->data['listSet']['gender'] = get_list_gender();
        $this->data['listSet']['status'] = get_list_active_inactive();
    }

    public function create(){
        $this->callPermission();

        $data = $this->data;

        //$this->data['listSet']['city_id'] = $listCity;
        $getProvince = Province::orderBy('name', 'ASC')->get();

        $data['viewType'] = 'create';
        $data['formsTitle'] = __('general.title_create', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['province'] = $getProvince;

        return view($this->listView[$data['viewType']], $data);
    }

    public function edit($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;
        $getProvince = Province::orderBy('name', 'ASC')->get();

        $data['viewType'] = 'edit';
        $data['formsTitle'] = __('general.title_edit', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;
        $data['province'] = $getProvince;
        $data['cityId'] = City::where('id', $getData->city_id)->first();
        $data['districtId'] = District::where('id', $getData->district_id)->first();
        $data['subDistrictId'] = SubDistrict::where('id', $getData->sub_district_id)->first();

        return view($this->listView[$data['viewType']], $data);
    }

    public function forgotPassword($id){
        $this->callPermission();

        $getData = $this->crud->show($id,[
            'id' => $id,
        ]);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $data['viewType'] = 'edit';
        $data['thisLabel'] = 'Password';
        $data['formsTitle'] = __('general.title_edit', ['field' => $data['thisLabel'].' '.$getData->fullname]);
        $data['passing'] = generatePassingData([

            'password' => [
                'type' => 'password',
                'validate' => [
                    'edit' => 'required|confirmed'
                ]
            ],
            'password_confirmation' => [
                'type' => 'password',
                'validate' => [
                    'edit' => 'required'
                ]
            ]
        ]);
        $data['data'] = $getData;

        return view($this->listView['forgotPassword'], $data);
    }

    public function updatePassword($id){

        $this->callPermission();

        $getData = $this->crud->show($id,[
            'id' => $id,
        ]);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->validate($this->request, [
                'password' => 'required',
                'password_confirmation' => 'required'
                ]);

        if($data['password'] != $data['password_confirmation']){
            return redirect()->back()->withInput()->withErrors(
                [
                    'password' => __('general.password_confirmation_different')
                ]
            );
        }
        $getData->password = app('hash')->make($data['password']);
        $getData->save();

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_change_password'));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }
    }

    public function show($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id, [
            'id' => $id,
        ]);

        //dd($getData);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $getProvince = Province::where('id', $getData->province_id)->first();
        $getCity = City::where('id',$getData->city_id)->first();
        $getDistrict = District::where('id', $getData->district_id)->first();
        $getSubDistrict = SubDistrict::where('id', $getData->sub_district_id)->first();

        //Transaction Product
        $getDataTransactionProduct =   Transaction::selectRaw('transaction.*, product_name, product_qty, product_price')
            ->leftJoin('users','users.id','=','transaction.user_id')
            ->leftJoin('transaction_details','transaction_details.transaction_id','=','transaction.id')
            ->where('users.id', $id)
            ->where('transaction.type_service', 1)
            ->orderBy('transaction.id','DESC')
            ->get();

        //Transaction Lab
        $getDataTransactionLab =   Transaction::selectRaw('transaction.*, lab_name, lab_price')
            ->leftJoin('users','users.id','=','transaction.user_id')
            ->leftJoin('transaction_details','transaction_details.transaction_id','=','transaction.id')
            ->where('users.id', $id)
            ->where('transaction.type_service', 3)
            ->orderBy('id','DESC')
            ->get();

        //Transaction Doctor
        $getDataTransactionDoctor =   Transaction::selectRaw('transaction.*, doctor_name, doctor_price')
            ->leftJoin('users','users.id','=','transaction.user_id')
            ->leftJoin('transaction_details','transaction_details.transaction_id','=','transaction.id')
            ->where('users.id', $id)
            ->where('transaction.type_service', 2)
            ->orderBy('id','DESC')
            ->get();


        // Appointment Lab
        $getAppointmentLab = AppointmentLab::selectRaw('appointment_lab.*, lab_name, lab_price')
            ->leftJoin('appointment_lab_details','appointment_lab_details.appointment_lab_id', '=' ,'appointment_lab.id')
            ->leftJoin('users','users.id','=','appointment_lab.user_id')
            ->where('users.id', $id)
            ->get();
//        // Appointment Doctor
        $getAppointmentDoctor = AppointmentDoctor::selectRaw('appointment_doctor.*, product_name, product_qty_checkout as product_qty, product_price')
            ->leftJoin('appointment_doctor_product','appointment_doctor_product.appointment_doctor_id', '=', 'appointment_doctor.id')
            ->leftJoin('users','users.id','=','appointment_doctor.user_id')
            ->where('users.id', $id)
            ->get();

        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;
        $data['province'] = $getProvince;
        $data['city'] = $getCity;
        $data['district'] = $getDistrict;
        $data['subDistrict'] = $getSubDistrict;
        $data['transactionProduct'] = $getDataTransactionProduct;
        $data['transactionDoctor'] = $getDataTransactionDoctor;
        $data['transactionLab'] = $getDataTransactionLab;
        $data['appointmentDoctor'] = $getAppointmentDoctor;
        $data['appointmentLab'] = $getAppointmentLab;
        $data['getListStatus'] = get_list_active_inactive();
        $data['getListStatusTransaction'] = get_list_transaction();
        $data['getListStatusAppointment'] = get_list_appointment();

        return view($this->listView[$data['viewType']], $data);
    }

    public function store(){

        $this->callPermission();

        $viewType = 'create';

        $getListCollectData = collectPassingData($this->passingData, $viewType);

        $validate = $this->setValidateData($getListCollectData, $viewType);
        if (count($validate) > 0)
        {
            $data = $this->validate($this->request, $validate);
        }
        else {
            $data = [];
            foreach ($getListCollectData as $key => $val) {
                $data[$key] = $this->request->get($key);
            }
        }

        unset($getListCollectData['upload_ktp_full']);


        $dokument = $this->request->file('upload_ktp_full');
        if ($dokument) {
            if ($dokument->getError() != 1) {

                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/users';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }

        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        unset($data['upload_ktp_full']);

        $data['province_id'] = $this->request->get('province_id');
        $data['city_id'] = $this->request->get('city_id');
        $data['district_id'] = $this->request->get('district_id');
        $data['sub_district_id'] = $this->request->get('sub_district_id');
        $data['zip_code'] = $this->request->get('zip_code');
        $data['address'] = $this->request->get('address');
        $data['address_detail'] = $this->request->get('address_detail');
        $data['upload_ktp'] = $dokumentImage;
        $data['password'] = bcrypt('123');
        $data['patient'] = 1;

        $getData = $this->crud->store($data);

        $addressDetail = [
            'address' => $getData->address,
            'address_detail' => $getData->address_detail,
            'city_id' => $getData->city_id,
            'district_id' => $getData->district_id,
            'sub_district_id' => $getData->sub_district_id,
            'zip_code' => $getData->zip_code,
        ];

        $usersAddress = new UsersAddress();
        $usersAddress->user_id = $getData->id;
        $usersAddress->city_id = $getData->city_id;
        $usersAddress->district_id = $getData->district_id;
        $usersAddress->sub_district_id = $getData->sub_district_id;
        $usersAddress->zip_code = $getData->zip_code;
        $usersAddress->address_name = $getData->address;
        $usersAddress->address = $getData->address_detail;
        $usersAddress->address_detail = json_encode($addressDetail);
        $usersAddress->save();

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.show', $id);
        }
    }

    public function update($id){

        $this->callPermission();

        $viewType = 'edit';

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getListCollectData = collectPassingData($this->passingData, $viewType);

        unset($getListCollectData['upload_ktp_full']);

        $validate = $this->setValidateData($getListCollectData, $viewType);
        if (count($validate) > 0)
        {
            $data = $this->validate($this->request, $validate);
        }
        else {
            $data = [];
            foreach ($getListCollectData as $key => $val) {
                $data[$key] = $this->request->get($key);
            }
        }

        $dokument = $this->request->file('upload_ktp_full');
        if ($dokument) {
            if ($dokument->getError() != 1) {

                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/users';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }
        else{
            $dokumentImage = $getData->upload_ktp;
        }


        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $data['upload_ktp'] = $dokumentImage;
        $data['province_id'] = $this->request->get('province_id');
        $data['city_id'] = $this->request->get('city_id');
        $data['district_id'] = $this->request->get('district_id');
        $data['sub_district_id'] = $this->request->get('sub_district_id');
        $data['zip_code'] = $this->request->get('zip_code');
        $data['address'] = $this->request->get('address');
        $data['address_detail'] = $this->request->get('address_detail');

        $getData = $this->crud->update($data, $id);

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.show', $id);
        }
    }
    public function dataTable()
    {
        $this->callPermission();

        //$userId = session()->get('admin_id');

        $dataTables = new DataTables();

        $builder = $this->model::query()->selectRaw('users.id, users.fullname, users.gender, users.email, users.phone, upload_ktp, klinik.name as klinik_id, users.status')
            ->where('users.patient', '=', 1)
            ->leftJoin('klinik','klinik.id','=','users.klinik_id')
            ->where('klinik.status',80);


        $dataTables = $dataTables->eloquent($builder)
            ->addColumn('action', function ($query) {
                return view($this->listView['dataTable'], [
                    'query' => $query,
                    'thisRoute' => $this->route,
                    'permission' => $this->permission,
                    'masterId' => $this->masterId
                ]);
            });

        $listRaw = [];
        $listRaw[] = 'action';
        foreach (collectPassingData($this->passingData) as $fieldName => $list) {
            if (in_array($list['type'], ['select', 'select2'])) {
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName) {
                    $getList = isset($this->data['listSet'][$fieldName]) ? $this->data['listSet'][$fieldName] : [];
                    return isset($getList[$query->$fieldName]) ? $getList[$query->$fieldName] : $query->$fieldName;
                });
            } else if (in_array($list['type'], ['money'])) {
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return number_format($query->$fieldName, 0);
                });
            } else if (in_array($list['type'], ['image'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<img src="' . asset($list['path'] . $query->$fieldName) . '" class="img-responsive max-image-preview"/>';
                });
            } else if (in_array($list['type'], ['image_preview'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<img src="' . $query->$fieldName . '" class="img-responsive max-image-preview"/>';
                });
            } else if (in_array($list['type'], ['code'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<pre>' . json_encode(json_decode($query->$fieldName, true), JSON_PRETTY_PRINT) . '"</pre>';
                });
            } else if (in_array($list['type'], ['texteditor'])) {
                $listRaw[] = $fieldName;
            }
        }

        return $dataTables
            ->rawColumns($listRaw)
            ->make(true);
    }

    public function findCity(){
        $s = $this->request->get('s');
        $provinceId = intval($this->request->get('province_id'));

        $getData = City::Where('province_id', 'LIKE', strip_tags($provinceId))->orderBy('name', 'ASC')->get();

        return response()->json($getData);
    }

    public function findDistrict(){
        $s = $this->request->get('s');
        $cityId = intval($this->request->get('city_id'));

        $getData = District::Where('city_id', 'LIKE', strip_tags($cityId))->orderBy('name', 'ASC')->get();

        return response()->json($getData);
    }

    public function findSubDistrict(){
        $s = $this->request->get('s');
        $districtId = intval($this->request->get('district_id'));

        $getData = SubDistrict::Where('district_id', 'LIKE', strip_tags($districtId))->orderBy('name', 'ASC')->get();

        return response()->json($getData);
    }
}
