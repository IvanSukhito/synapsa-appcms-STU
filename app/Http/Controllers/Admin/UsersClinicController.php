<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\Province;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use App\Codes\MOdels\V1\UsersAddress;
use App\Codes\Models\V1\Klinik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class UsersClinicController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
//            'klinik_id' => [
//                'validate' => [
//                    'create' => 'required',
//                    'edit' => 'required'
//                ],
//                'lang' => 'general.klinik',
//                'type' => 'select2',
//            ],
            'fullname' => [
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
                'lang' => 'KTP',
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
            $request, 'general.users_clinic', 'user-clinic', 'V1\Users', 'user-clinic',
            $passingData
        );

        $getKlinik = Klinik::where('status', 80)->pluck('name', 'id')->toArray();
        if($getKlinik) {
            foreach($getKlinik as $key => $value) {
                $listKlinik[$key] = $value;
            }
        }


        $this->data['listSet']['klinik_id'] = $listKlinik;
        $this->data['listSet']['gender'] = get_list_gender();
        $this->data['listSet']['status'] = get_list_active_inactive();
        $this->listView['dataTable'] = env('ADMIN_TEMPLATE').'.page.user-clinic.list_button';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.user-clinic.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.user-clinic.forms2';

    }

    public function show($id)
    {
        $this->callPermission();
        $adminClinicId = session()->get('admin_clinic_id');

        $getData = $this->crud->show($id, [
            'id' => $id,
            'klinik_id' => $adminClinicId
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
                                ->where('transaction.klinik_id', $adminClinicId)
                                ->where('transaction.type_service', 1)
                                ->orderBy('transaction.id','DESC')
                                ->get();

        //Transaction Lab
        $getDataTransactionLab =   Transaction::selectRaw('transaction.*, lab_name, lab_price')
            ->leftJoin('users','users.id','=','transaction.user_id')
            ->leftJoin('transaction_details','transaction_details.transaction_id','=','transaction.id')
            ->where('transaction.klinik_id', $adminClinicId)
            ->where('users.id', $id)
            ->where('transaction.type_service', 3)
            ->orderBy('id','DESC')
            ->get();

        //Transaction Doctor
        $getDataTransactionDoctor =   Transaction::selectRaw('transaction.*, doctor_name, doctor_price')
            ->leftJoin('users','users.id','=','transaction.user_id')
            ->leftJoin('transaction_details','transaction_details.transaction_id','=','transaction.id')
            ->where('transaction.klinik_id', $adminClinicId)
            ->where('users.id', $id)
            ->where('transaction.type_service', 2)
            ->orderBy('id','DESC')
            ->get();


        // Appointment Lab
        $getAppointmentLab = AppointmentLab::selectRaw('appointment_lab.*, lab_name, lab_price')
                             ->leftJoin('appointment_lab_details','appointment_lab_details.appointment_lab_id', '=' ,'appointment_lab.id')
                             ->leftJoin('users','users.id','=','appointment_lab.user_id')
                             ->where('users.id', $id)
                             ->where('appointment_lab.klinik_id', $adminClinicId)
                             ->get();
//        // Appointment Doctor
        $getAppointmentDoctor = AppointmentDoctor::selectRaw('appointment_doctor.*, product_name, product_qty_checkout as product_qty, product_price')
                                ->leftJoin('appointment_doctor_product','appointment_doctor_product.appointment_doctor_id', '=', 'appointment_doctor.id')
                                ->leftJoin('users','users.id','=','appointment_doctor.user_id')
                                ->where('appointment_doctor.klinik_id', $adminClinicId)
                                ->where('users.id', $id)
                                ->get();

        //$appointment = $getDataAppointment->orderBy('id','DESC')->orderBy('date','DESC')->get();

        $data['listSet']['status'] = get_list_active_inactive();
        $data['listSet']['statusTransaction'] = get_list_transaction();

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
        $data['getListStatus'] = $data['listSet']['status'];
        $data['getListStatusTransaction'] = $data['listSet']['statusTransaction'];
        $data['getListStatusAppointment'] = get_list_appointment();

        return view($this->listView[$data['viewType']], $data);
    }

    public function store(){

        $this->callPermission();

        $viewType = 'create';

        $getListCollectData = collectPassingData($this->passingData, $viewType);

        unset($getListCollectData['upload_ktp']);

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

        $dokument = $this->request->file('upload_ktp');
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
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
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
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }
    }
    public function dataTable()
    {
        $this->callPermission();

        $klinik_id = session()->get('admin_clinic_id');

        $dataTables = new DataTables();

        $builder = $this->model::query()->selectRaw('users.id, users.fullname, users.gender, users.email, users.phone, upload_ktp, klinik.name as klinik_id, users.status')
            ->leftJoin('klinik','klinik.id','=','users.klinik_id')
            ->where('users.patient', '=', 1)
            ->where('users.status',80)
            ->where('users.klinik_id', $klinik_id);


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

}
