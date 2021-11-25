<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Admin;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\DoctorService;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\LabSchedule;
use App\Codes\Models\V1\Province;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;
use App\Codes\Models\V1\Service;
use Illuminate\Http\Request;

class DoctorClinicController extends _CrudController
{
    protected $limit;

    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'user_id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'general.name',
                'type' => 'select2',
            ],
            'doctor_category_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.doctor-category',
                'type' => 'select2',
            ],
            'formal_edu' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'texteditor',
                'list' => 0,
            ],
            'nonformal_edu' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'texteditor',
                'list' => 0,
            ],
            'service_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => ''
                ],
                'type' => 'multiselect2',
                'show' => 0,
                'list' => 0,
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'Aksi',
            ]
        ];
        $this->passingUser = generatePassingData([
            'fullname' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ]
            ],
            'address' => [
                'type' => 'texteditor',
                'list' => 0,
                'create' => 0,
                'edit' => 0,
                'show' => 0,
            ],
            'address_detail' => [
                'type' => 'texteditor',
                'list' => 0,
                'create' => 0,
                'edit' => 0,
                'show' => 0,
            ],
            'zip_code' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'create' => 0,
                'edit' => 0,
                'show' => 0,
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
                'list' => 0
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
            ]
        ]);


        parent::__construct(
            $request, 'general.doctor_clinic', 'doctor_clinic', 'V1\Doctor', 'doctor_clinic',
            $passingData
        );

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();

        $getUsers = Users::where('status', 80)->where('doctor',1)->where('klinik_id', $getAdmin->klinik_id)->pluck('fullname', 'id')->toArray();
        $listUsers = [];
        if($getUsers) {
            foreach($getUsers as $key => $value) {
                $listUsers[$key] = $value;
            }
        }

        $getDoctorCategory = DoctorCategory::pluck('name', 'id')->toArray();
        $listDoctorCategory = [];
        if($getDoctorCategory) {
            foreach($getDoctorCategory as $key => $value) {
                $listDoctorCategory[$key] = $value;
            }
        }

        $service_id = [];
        foreach(Service::where('status', 80)->pluck('name', 'id')->toArray() as $key => $val) {
            $service_id[$key] = $val;
        };

        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.forms';
        $this->listView['create2'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.forms2';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.forms_edit';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.forms';
        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.list';

        $this->listView['schedule'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.schedule';

        $this->data['listSet']['user_id'] = $listUsers;
        $this->data['listSet']['service_id'] = $service_id;
        $this->data['listSet']['doctor_category_id'] = $listDoctorCategory;
        $this->data['listSet']['gender'] = get_list_gender();
        $this->data['listSet']['status'] = get_list_active_inactive();
        $this->data['listSet']['weekday'] = get_list_weekday();
        $this->data['listSet']['schedule_type'] = get_list_schedule_type();
        $this->listView['dataTable'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.list_button';

    }

    public function create()
    {
        $this->callPermission();

        $data = $this->data;
        $getProvince = Province::get();


        $data['viewType'] = 'create';
        $data['formsTitle'] = __('general.title_create', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['passing1'] = collectPassingData($this->passingUser, $data['viewType']);
        $data['province'] = $getProvince;


        return view($this->listView[$data['viewType']], $data);
    }

    public function dataTable()
    {
        $this->callPermission();

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();
        $dataTables = new DataTables();

        $builder = $this->model::query()->selectRaw('doctor.id as id, users.fullname as user_id, doctor_category.name as doctor_category_id')
            ->join('users','users.id', '=', 'doctor.user_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->where('users.doctor',1)
            ->where('users.klinik_id', $getAdmin->klinik_id);

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

    public function edit($id){
        $this->callPermission();


        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;
        $getProvince = Province::get();

        $getDoctorService = DoctorService::where('doctor_id',$id)->get();
        $getDataUser = Users::where('id', $getData->user_id)->first();

        $data['thisLabel'] = __('general.doctor');
        $data['viewType'] = 'edit';
        $data['formsTitle'] = __('general.title_create', ['field' => __('general.doctor') . ' ' . $getData->name]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['passing1'] = collectPassingData($this->passingUser, $data['viewType']);
        $data['province'] = $getProvince;
        $data['data'] = $getData;
        $data['dataUser'] = $getDataUser;
        $data['cityId'] = City::where('id', $getDataUser->city_id)->first();
        $data['districtId'] = District::where('id', $getDataUser->district_id)->first();
        $data['subDistrictId'] = SubDistrict::where('id', $getDataUser->sub_district_id)->first();
        $data['doctorService'] = $getDoctorService;

        return view($this->listView[$data['viewType']], $data);
    }

    public function store()
    {
        $this->callPermission();

        $this->validate($this->request, [
            'service_id' => 'required',
            'price' => 'required'
        ]);

        $viewType = 'create';

        $getListCollectData = collectPassingData($this->passingData, $viewType);
        $getListCollectData2 = collectPassingData($this->passingUser, $viewType);

        unset($getListCollectData['service_id']);

        //validate data2
        $validate = $this->setValidateData($getListCollectData2, $viewType);
        if (count($validate) > 0)
        {
            $data2 = $this->validate($this->request, $validate);
        }

        unset($data2['upload_ktp_full']);

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

        $data2['klinik_id'] = session()->get('admin_clinic_id');
        $data2['province_id'] = $this->request->get('province_id');
        $data2['city_id'] = $this->request->get('city_id');
        $data2['district_id'] = $this->request->get('district_id');
        $data2['sub_district_id'] = $this->request->get('sub_district_id');
        $data2['zip_code'] = $this->request->get('zip_code');
        $data2['address'] = $this->request->get('address');
        $data2['address_detail'] = $this->request->get('address_detail');
        $data2['upload_ktp'] = $dokumentImage;
        $data2['password'] = bcrypt('123');
        $data2['doctor'] = 1;

        //dd($data2);
        if($data2){
            $user = Users::create($data2);
        }

        //validate dataServive
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

        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $data['user_id'] = $user->id;

        $getData = $this->crud->store($data);
        $serviceId = $this->request->get('service_id');
        $price = clear_money_format($this->request->get('price'));

        foreach($serviceId as $key => $list){

            DoctorService::create([
                'doctor_id' => $getData->id,
                'service_id' => $list,
                'price' => $price[$key] != null ? $price[$key] : 0
            ]);
        }

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_edit_', ['field' => $this->data['thisLabel']]));
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

        //update user dan validate user
        $getDataUser = Users::where('id', $getData->user_id)->first();
        $getListCollectData2 = collectPassingData($this->passingUser, $viewType);

        unset($getListCollectData2['upload_ktp_full']);

        $validate = $this->setValidateData($getListCollectData2, $viewType, $getDataUser->id);
        if (count($validate) > 0)
        {
            $data2 = $this->validate($this->request, $validate);
        }
        else {
            $data2 = [];
            foreach ($getListCollectData2 as $key => $val) {
                $data2[$key] = $this->request->get($key);
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
        }else{

            $dokumentImage = $getDataUser->upload_ktp;

        }

        $data2['province_id'] = $this->request->get('province_id');
        $data2['city_id'] = $this->request->get('city_id');
        $data2['district_id'] = $this->request->get('district_id');
        $data2['sub_district_id'] = $this->request->get('sub_district_id');
        $data2['zip_code'] = $this->request->get('zip_code');
        $data2['address'] = $this->request->get('address');
        $data2['address_detail'] = $this->request->get('address_detail');
        $data2['upload_ktp'] = $dokumentImage;
        $data2['doctor'] = 1;

        //dd($data2);
        if($data2){
            $user = $getDataUser->update($data2);
        }

        //update service dan validate service
        $getListCollectData = collectPassingData($this->passingData, $viewType);
        unset($getListCollectData['service_id']);

        $validate = $this->setValidateData($getListCollectData, $viewType, $id);
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

        $data = $this->getCollectedData($getListCollectData, $viewType, $data, $getData);

        $getData = $this->crud->update($data, $id);

        $serviceId = $this->request->get('service_id');
        $price = clear_money_format($this->request->get('price'));

        if($serviceId){
            $saveDataTemp = [];
            $doctor = Doctor::where('id', $id)->first();
            foreach($serviceId as $key => $list){
                $prices = $price[$key] != null ? $price[$key] : 0;

                $saveDataTemp[$list] = [
                    'price' => $prices
                ];
            }
            $doctor->getService()->sync($saveDataTemp);
        }

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
    public function show($id)
    {
        $this->callPermission();

        $now = Carbon::now();

        $listSetCarbonDay = get_list_carbon_day();
        $weekStartDate = $now->startOfWeek()->format('Y-m-d');
        $weekEndDate = $now->endOfWeek()->format('Y-m-d');

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getScheduleData  = DoctorSchedule::selectRaw('doctor_schedule.id, doctor_schedule.date_available,
         doctor_schedule.time_start, doctor_schedule.time_end, doctor_schedule.book, doctor_schedule.weekday,
         B.fullname AS doctor_id, C.name AS service_id')
            ->where('doctor_schedule.doctor_id', '=', $id)
            ->leftJoin('users AS B', 'B.id', '=', 'doctor_schedule.doctor_id')
            ->leftJoin('service AS C', 'C.id', '=', 'doctor_schedule.service_id')->get();

        $temp = [];
        foreach($getScheduleData as $schedule) {
            if(($weekStartDate <= $schedule->date_available && $weekEndDate >= $schedule->date_available) || $schedule->date_available == null) {
                $found = 0;
                if ($schedule->weekday > 0) {
                    $now = Carbon::now();
                    $weekDayDate = $now->startOfWeek($listSetCarbonDay[$schedule->weekday])->format('Y-m-d');

                    $findSchedule = DoctorSchedule::where('doctor_schedule.doctor_id', '=', $id)
                        ->where('date_available', $weekDayDate)
                        ->get();

                    if (count($findSchedule) > 0) {
                        $found = 1;
                    }
                }

                if ($schedule->date_available != null) {
                    $schedule->weekday = Carbon::parse($schedule->date_available)->dayOfWeekIso;
                }

                if ($found == 0) {
                    $temp[] = $schedule;
                }
            }
        }

        $getScheduleData = $temp;

        $data = $this->data;

        $getDataUser = Users::where('id', $getData->user_id)->first();
        $getDoctorService = DoctorService::where('doctor_id',$id)->get();

        $getProvince = Province::where('id', $getDataUser->province_id)->first();
        $getCity = City::where('id',$getDataUser->city_id)->first();
        $getDistrict = District::where('id', $getDataUser->district_id)->first();
        $getSubDistrict = SubDistrict::where('id', $getDataUser->sub_district_id)->first();

        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['passing1'] = collectPassingData($this->passingUser, $data['viewType']);
        $data['province'] = $getProvince;
        $data['data'] = $getData;
        $data['dataUser'] = $getDataUser;
        $data['doctorService'] = $getDoctorService;
        $data['getScheduleData'] = $getScheduleData;
        $data['province'] = $getProvince;
        $data['city'] = $getCity;
        $data['district'] = $getDistrict;
        $data['subDistrict'] = $getSubDistrict;
        $data['getListAvailable'] = get_list_available();

        return view($this->listView[$data['viewType']], $data);
    }

    public function schedule($id)
    {
        $this->callPermission();

        $getDoctor = Doctor::selectRaw('doctor.id, fullname AS name')->join('users', 'users.id', 'doctor.user_id')->where('doctor.id', $id)->first();
        if (!$getDoctor) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $listSetCarbonDay = get_list_carbon_day();

        $now = Carbon::now();

        $serviceTelemed = Service::select('id')->where('name', 'LIKE', '%Telemed%')->first();
        $telemedId = $serviceTelemed->id;

        $getListDate = DoctorSchedule::select('date_available')
            ->where('doctor_id', $getDoctor->id)
            ->whereIn('type', [0,2])
            ->where('date_available', '!=', null)
            ->groupBy('date_available')
            ->orderBy('date_available', 'ASC')
            ->get();

        $getListDay = DoctorSchedule::select('weekday')
            ->where('doctor_id', $getDoctor->id)
            ->where('type', 1)
            ->groupBy('weekday')
            ->orderBy('weekday', 'ASC')
            ->get();

        $getListWeekday = $this->data['listSet']['weekday'];

        $getTargetDay = $this->request->get('date') > 0 ? $this->request->get('date') : Carbon::now()->dayOfWeekIso;

        $notFound = 1;
        $findFirstDay = '';
        $temp = [];
        foreach ($getListDay as $list) {
            $temp[$list->weekday] = $getListWeekday[$list->weekday];
            if (strlen($findFirstDay) <= 0) {
                $findFirstDay = $list->weekday;
            }
            if ($getTargetDay == $list->weekday) {
                $notFound = 0;
            }
        }

        if(count($getListDate) > 0) {
            foreach($getListDate as $list) {
                $startDate = $now->startOfWeek()->format('Y-m-d');
                $endDate = $now->endOfWeek()->format('Y-m-d');
                if($list->date_available >= $startDate && $list->date_available <= $endDate) {
                    $date = Carbon::parse($list->date_available)->dayOfWeekIso;
                    $temp[$date] = $getListWeekday[$date];
                    if (strlen($findFirstDay) <= 0) {
                        $findFirstDay = $date;
                    }
                    if ($getTargetDay == $date) {
                        $notFound = 0;
                    }
                }
            }
        }

        $getListDay = $temp;

        if ($notFound == 1 && strlen($findFirstDay) > 0) {
            $getTargetDay = $findFirstDay;
        }

        $weekStartDate = $now->startOfWeek($listSetCarbonDay[$getTargetDay])->format('Y-m-d');

        $checkGetListSchedule = DoctorSchedule::where('date_available', $weekStartDate)
            ->where('doctor_id', $getDoctor->id)
            ->whereIn('type', [0,2])
            ->get();

        if(count($checkGetListSchedule) > 0) {
            $getTargetDay = $weekStartDate;
        }

        if(in_array($getTargetDay, [1,2,3,4,5,6,7])) {
            $getData = DoctorSchedule::where('weekday', $getTargetDay)
                ->where('doctor_id', $getDoctor->id)
                ->orderBy('id', 'DESC')
                ->get();

            $scheduleType = 1;
        }
        else {
            $getData = DoctorSchedule::where('date_available', $getTargetDay)
                ->where('doctor_id', $getDoctor->id)
                ->orderBy('id', 'DESC')
                ->get();

            $scheduleType = 2;

            $getTargetDay = date('w', strtotime($getTargetDay));
        }

        $getDoctorService = DoctorService::where('doctor_id', $id)->get()->toArray();

        $service_id = [];
        foreach($getDoctorService as $index => $val){
            $service_id[] = $val['service_id'];
        }

        $service = [];
        foreach(Service::where('status', 80)->whereIn('id', $service_id)->pluck('name', 'id')->toArray() as $key => $val) {
            $service[$key] = $val;
        }

        $data = $this->data;
        $data['parentLabel'] = $data['thisLabel'];
        $data['thisLabel'] = __('general.doctor_schedule');
        $data['listSet']['service'] = $service;
        $data['getDoctor'] = $getDoctor;
        $data['getListDay'] = $getListDay;
        $data['getTargetDay'] = $getTargetDay;
        $data['getListWeekday'] = $getListWeekday;
        $data['scheduleType'] = $scheduleType;
        $data['telemedId'] = $telemedId;
        $data['getData'] = $getData;

        return view($this->listView['schedule'], $data);
    }

    public function storeSchedule($id)
    {
        $this->callPermission();

        $this->validate($this->request, [
            'schedule_type' => 'required',
        ]);

        if($this->request->get('schedule_type') == 1) {
            $data = $this->validate($this->request, [
                'service' => 'required',
                'time_start' => 'required',
                'time_end' => 'required',
                'weekday' => 'required',
            ]);

            $getWeekday = intval($data['weekday']);
            $getServiceId = intval($data['service']);
            $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
            $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

            DoctorSchedule::create([
                'doctor_id' => $id,
                'service_id' => $getServiceId,
                'weekday' => $getWeekday,
                'time_start' => $getTimeStart,
                'time_end' => $getTimeEnd,
                'type' => 1,
                'book' => 80
            ]);
        }
        else {
            $data = $this->validate($this->request, [
                'service' => 'required',
                'time_start' => 'required',
                'time_end' => 'required',
                'date' => 'required',
            ]);

            $getDate = strtotime($data['date']) > 0 ? date('Y-m-d', strtotime($data['date'])) : date('Y-m-d', strtotime("+1 day"));
            $getServiceId = intval($data['service']);
            $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
            $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

            DoctorSchedule::create([
                'doctor_id' => $id,
                'service_id' => $getServiceId,
                'date_available' => $getDate,
                'time_start' => $getTimeStart,
                'time_end' => $getTimeEnd,
                'type' => 2,
                'book' => 80
            ]);
        }

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.schedule', $id);
        }
    }

    public function updateSchedule($id, $scheduleId)
    {
        $this->callPermission();

        $this->validate($this->request, [
            'schedule_type' => 'required',
        ]);

        $getData = DoctorSchedule::where('doctor_id', $id)->where('id', $scheduleId)->first();
        if (!$getData) {
            if($this->request->ajax()){
                return response()->json(['result' => 2, 'message' => __('general.error_not_found')]);
            }
            else {
                session()->flash('message', __('general.error_not_found'));
                session()->flash('message_alert', 1);
                return redirect()->route($this->rootRoute.'.' . $this->route . '.schedule', $id);
            }
        }

        if($this->request->get('schedule_type') == 1) {
            $data = $this->validate($this->request, [
                'service' => 'required',
                'time_start' => 'required',
                'time_end' => 'required',
                'weekday' => 'required',
            ]);

            $getWeekday = intval($data['weekday']);
            $getServiceId = intval($data['service']);
            $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
            $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

            $getData->doctor_id = $id;
            $getData->service_id = $getServiceId;
            $getData->weekday = $getWeekday;
            $getData->time_start = $getTimeStart;
            $getData->time_end = $getTimeEnd;
        }
        else {
            $data = $this->validate($this->request, [
                'service' => 'required',
                'time_start' => 'required',
                'time_end' => 'required',
                'date' => 'required',
            ]);

            $getDate = strtotime($data['date']) > 0 ? date('Y-m-d', strtotime($data['date'])) : date('Y-m-d', strtotime("+1 day"));
            $getServiceId = intval($data['service']);
            $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
            $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

            $getData->doctor_id = $id;
            $getData->service_id = $getServiceId;
            $getData->date_available = $getDate;
            $getData->time_start = $getTimeStart;
            $getData->time_end = $getTimeEnd;
        }

        $getData->save();

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_edit_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_edit_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.schedule', $id);
        }
    }

    public function destroySchedule($id, $scheduleId)
    {
        $this->callPermission();

        $getData = DoctorSchedule::where('doctor_id', $id)->where('id', $scheduleId)->first();
        if (!$getData) {
            if($this->request->ajax()){
                return response()->json(['result' => 2, 'message' => __('general.error_not_found')]);
            }
            else {
                session()->flash('message', __('general.error_not_found'));
                session()->flash('message_alert', 1);
                return redirect()->route($this->rootRoute.'.' . $this->route . '.schedule', $id);
            }
        }

        $getData->delete();

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_delete_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_delete_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.schedule', $id);
        }
    }

    public function create2(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getData = Users::where('id', $adminId)->first();
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        if($this->request->get('download_example_import')) {
            $getLogic = new SynapsaLogic();
            $getLogic->downloadExampleImportDoctorClinic();
        }

        $data = $this->data;

        $data['thisLabel'] = __('general.doctor');
        $data['viewType'] = 'create';
        $data['type'] = 'doctor';
        $data['formsTitle'] = __('general.title_create', ['field' => __('general.doctor')]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;

        return view($this->listView['create2'], $data);
    }

    public function store2(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getAdmin = Admin::where('id', $adminId)->first();
        if (!$getAdmin) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $this->validate($this->request, [
            'import_doctor' => 'required',
        ]);

        //A-N
        //A = Nomor
        //B = Nama Doctor
        //C = DOB
        //D = Gender
        //E = NIK
        //F = Phone
        //G = EMAIL
        //H = Kategori
        //I = Formal Education
        //J = Non-Formal Education
        //K = Telemed
        //L = Homecare
        //M = Visit
        //N = Harga Telemed
        //O = Harga Homecare
        //P = Harga Visit
        //Start From Row 6

        $getFile = $this->request->file('import_doctor');

        if($getFile) {
//            $destinationPath = 'synapsaapps/doctor-clinic/example_import';
//
//            $getUrl = Storage::put($destinationPath, $getFile);
//
//            die(env('OSS_URL') . '/' . $getUrl);

            try {
                $getFileName = $getFile->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                if (in_array(strtolower($ext), ['xlsx', 'xls'])) {
                    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($getFile);
                    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                    $data = $reader->load($getFile);

                    if ($data) {
                        $spreadsheet = $data->getActiveSheet();
                        foreach ($spreadsheet->getRowIterator() as $key => $row) {
                            if($key >= 6) {
                                $fullname = $spreadsheet->getCell("B" . $key)->getValue();
                                $dob = $spreadsheet->getCell("C". $key)->getValue();
                                $gender = $spreadsheet->getCell("D". $key)->getValue();
                                $nik = $spreadsheet->getCell("E". $key)->getValue();
                                $phone = $spreadsheet->getCell("F". $key)->getValue();
                                $email = $spreadsheet->getCell("G". $key)->getValue();
                                $kategori = strtolower(str_replace('','', $spreadsheet->getCell("H" . $key)->getValue()));
                                $formalEducation = $spreadsheet->getCell("I" . $key)->getValue();
                                $nonFormalEducation = $spreadsheet->getCell("J" . $key)->getValue();
                                $telemed = $spreadsheet->getCell("K" . $key)->getValue();
                                $homecare = $spreadsheet->getCell("L" . $key)->getValue();
                                $visit = $spreadsheet->getCell("M" . $key)->getValue();
                                $hargaTelemed = $spreadsheet->getCell("N" . $key)->getValue();
                                $hargaHomecare = $spreadsheet->getCell("O" . $key)->getValue();
                                $hargaVisit = $spreadsheet->getCell("P" . $key)->getValue();

                                $kategoriCheck = DoctorCategory::where('name', $kategori)->first();
                                if($kategoriCheck) {
                                    $kategori = $kategoriCheck->id;
                                }
                                else {
                                    if(strlen($kategori) > 0) {
                                        $saveCategory = [
                                            'name' => $kategori,
                                            'status' => 80
                                        ];

                                        $doctorCategory = DoctorCategory::create($saveCategory);
                                        $kategori = $doctorCategory->id;
                                    }
                                }

                                $klinik_id = session()->get('admin_clinic_id');

                                if($gender == 'Pria'){
                                    $gender = 1;
                                }
                                else{
                                    $gender = 2;
                                }

                                $saveDataUser = [
                                    'fullname' => $fullname,
                                    'dob' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dob)->format('Y-m-d'),
                                    'gender' => $gender,
                                    'klinik_id' => $klinik_id,
                                    'nik' => $nik,
                                    'phone' => $phone,
                                    'email' => $email,
                                    'doctor' => 1,
                                    'password' => bcrypt('123'),
                                ];

                                $user = Users::create($saveDataUser);

                                if($user) {
                                    $saveData = [
                                        'user_id' => $user->id,
                                        'doctor_category_id' => $kategori,
                                        'formal_edu' => $formalEducation,
                                        'nonformal_edu' => $nonFormalEducation,
                                    ];

                                    $doctor = Doctor::create($saveData);

                                    $telemedCheck = Service::where('name', 'Telemed')->first();
                                    $homecareCheck = Service::where('name', 'Homecare')->first();
                                    $visitCheck = Service::where('name', 'Visit')->first();

                                    $service = [];
                                    if(intval($telemed) == 1) {
                                        $service[$telemedCheck->id] = $hargaTelemed;
                                    }
                                    if(intval($homecare) == 1) {
                                        $service[$homecareCheck->id] = $hargaHomecare;
                                    }
                                    if(intval($visit) == 1) {
                                        $service[$visitCheck->id] = $hargaVisit;
                                    }

                                    foreach($service as $id => $val) {
                                        $doctor->getService()->attach($id, ['price' => $val]);
                                    }

                                    $id = $doctor->id;
                                }
                            }
                        }
                    }
                }
            }
            catch(\Exception $e) {
                isset($doctor) ?? $doctor->delete();
                isset($doctorCategory) ?? $doctorCategory->delete();

                session()->flash('message', __('general.failed_import_doctor'));
                session()->flash('message_alert', 1);
                return redirect()->route($this->rootRoute.'.' . $this->route . '.create2');
            }
        }

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.show', $id);
        }
    }

    public function createSchedule2($id){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getAdmin = Admin::where('id', $adminId)->first();
        if (!$getAdmin) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        if($this->request->get('download_example_import')) {
            $getLogic = new SynapsaLogic();
            $getLogic->downloadExampleImportDoctorClinicSchedule();
        }

        $data = $this->data;

        $getData = $this->crud->show($id);

        $data['thisLabel'] = __('general.doctor_schedule');
        $data['type'] = 'schedule';
        $data['viewType'] = 'create';
        $data['formsTitle'] = __('general.title_create', ['field' => __('general.doctor_schedule')]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;

        return view($this->listView['create2'], $data);
    }

    public function storeSchedule2($id){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getAdmin = Admin::where('id', $adminId)->first();
        if (!$getAdmin) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $this->validate($this->request, [
            'import_doctor_schedule' => 'required',
        ]);

        //A-N
        //A = Nomor
        //B = Service
        //C = Date
        //D = Time Start
        //E = Time End

        //Start From Row 6

        $getFile = $this->request->file('import_doctor_schedule');

        if($getFile) {

//            $destinationPath = 'synapsaapps/doctor-schedule/example_import';
//
//            $getUrl = Storage::put($destinationPath, $getFile);
//
//            die(env('OSS_URL') . '/' . $getUrl);

            try {
                $getFileName = $getFile->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                if (in_array(strtolower($ext), ['xlsx', 'xls'])) {
                    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($getFile);
                    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                    $data = $reader->load($getFile);

                    if ($data) {
                        $spreadsheet = $data->getActiveSheet();
                        foreach ($spreadsheet->getRowIterator() as $key => $row) {
                            if($key >= 6) {
                                $getService = $spreadsheet->getCell("B" . $key)->getValue();
                                $getDate = $spreadsheet->getCell("C" . $key)->getValue();
                                $getTimeStart = $spreadsheet->getCell("D" . $key)->getValue();
                                $getTimeEnd = $spreadsheet->getCell("E" . $key)->getValue();

                                $klinik_id = session()->get('admin_clinic_id');

                                if($klinik_id){
                                    $saveData = [
                                        'klinik_id' => $klinik_id,
                                        'doctor_id' => $id,
                                        'service_id' => $getService,
                                        'date_available' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($getDate)->format('Y-m-d'),
                                        'time_start' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($getTimeStart)->format('H:i:s'),
                                        'time_end' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($getTimeEnd)->format('H:i:s'),
                                        'book' => 80,
                                    ];

                                    $DoctorSchedule = DoctorSchedule::create($saveData);
                                }
                            }
                        }
                    }
                }
            }
            catch(\Exception $e) {
                // $labSchedule->delete();

                session()->flash('message', __('general.failed_import_doctor_schedule'));
                session()->flash('message_alert', 1);
                return redirect()->route($this->rootRoute.'.' . $this->route . '.create2');
            }
        }

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.schedule', $id);
        }
    }

}
