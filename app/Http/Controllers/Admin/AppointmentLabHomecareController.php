<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\AppointmentLabDetails;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\Province;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Users;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;


class AppointmentLabHomecareController extends _CrudController
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
                'create' => 0,
                'edit' => 0,
                'type' => 'select',
                'lang' => 'general.klinik',
                'custom' => ', name: "klinik.name"'
            ],
            'patient_name' => [
                'create' => 0,
                'edit' => 0,
            ],
            'patient_email' => [
                'create' => 0,
                'edit' => 0,
            ],
            'type_appointment' => [
                'create' => 0,
                'edit' => 0,
                'list' => 0,
            ],
            'time_book' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'general.book_time',
            ],
            'date' => [
                'create' => 0,
                'edit' => 0,
                'lang' => 'general.book_date',
            ],
            'time_start' => [
                'create' => 0,
                'edit' => 0,
                'list' => 0,
            ],
            'time_end' => [
                'create' => 0,
                'edit' => 0,
                'list' => 0,
            ],
            'layanan_lab' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'custom' => ', name:"appointment_lab_details.lab_name"'
            ],
            'total_test' => [
                'create' => 0,
                'edit' => 0,
                'list' => 0,
                'show' => 0,
            ],
            'status' => [
                'create' => 0,
                'edit' => 0,
                'type' => 'select'
            ],
            'created_at' => [
                'create' => 0,
                'edit' => 0,
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'Aksi',
            ]
        ];


        parent::__construct(
            $request, 'general.appointment_lab_homecare', 'appointment-lab-homecare', 'V1\AppointmentLab', 'appointment-lab-homecare',
            $passingData
        );

        $getUsers = Users::where('status', 80)->pluck('fullname', 'id')->toArray();
        $listUsers = [];
        if($getUsers) {
            foreach($getUsers as $key => $value) {
                $listUsers[$key] = $value;
            }
        }

        $service_id = [];
        foreach(Service::where('status', 80)->pluck('name', 'id')->toArray() as $key => $val) {
            $service_id[$key] = $val;
        }

        $status = [];
        foreach(get_list_appointment() as $key => $val) {
            $status[$key] = $val;
        }

        $klinik_id = [0 => 'All'];
        foreach(Klinik::where('status', 80)->pluck('name', 'id')->toArray() as $key => $val) {
            $klinik_id[$key] = $val;
        }

        $this->data['listSet']['user_id'] =  $listUsers;
        $this->data['listSet']['service_id'] = $service_id;
        $this->data['listSet']['status'] = $status;
        $this->data['listSet']['klinik_id'] = $klinik_id;

        $this->listView['dataTable'] = env('ADMIN_TEMPLATE').'.page.appointment-lab.list_button';
        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.appointment-lab.list';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.appointment-lab.forms';
        $this->listView['uploadHasilLab'] = env('ADMIN_TEMPLATE').'.page.appointment-lab.forms2';
        $this->listView['timeBook'] = env('ADMIN_TEMPLATE').'.page._view.time_book';

    }

    public function index()
    {
        $this->callPermission();

        $data = $this->data;

        $data['passing'] = collectPassingData($this->passingData);

        return view($this->listView['index'], $data);

    }

    public function show($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $getDataUser =  Users::where('id', $getData->user_id)->first();
        if($getDataUser) {
            $data['province'] = Province::where('id', $getDataUser->province_id)->first();
            $data['city'] = City::where('id', $getDataUser->city_id)->first();
            $data['district'] = District::where('id', $getDataUser->district_id)->first();
            $data['subDistrict'] = SubDistrict::where('id', $getDataUser->sub_district_id)->first();
        }

        $getDataDetails = AppointmentLabDetails::where('appointment_lab_id', $id)->get();

        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;
        $data['dataDetails'] = $getDataDetails;
        $data['dataUser'] = $getDataUser;

        return view($this->listView[$data['viewType']], $data);
    }

    public function dataTable()
   {
       $this->callPermission();

       $dataTables = new DataTables();

       $builder = $this->model::query()->selectRaw('appointment_lab.id, service.name as service, users.fullname as user,
       patient_name, patient_email, type_appointment,  appointment_lab.date as date, time_start, time_end, total_test,
       appointment_lab.status, appointment_lab.created_at, GROUP_CONCAT(" ", appointment_lab_details.lab_name) as layanan_lab,
       appointment_lab.klinik_id, klinik.name')
           ->leftJoin('service', 'service.id', '=', 'appointment_lab.service_id')
           ->leftJoin('users', 'users.id', '=', 'appointment_lab.user_id')
           ->leftJoin('klinik', 'klinik.id', '=', 'appointment_lab.klinik_id')
           ->leftJoin('appointment_lab_details', 'appointment_lab_details.appointment_lab_id', '=', 'appointment_lab.id')
           ->where('type_appointment', 'Homecare')
           ->groupByRaw('service, user, patient_name, patient_email, type_appointment, date, time_start, time_end, total_test,
            appointment_lab.id, status, created_at, klinik_id, klinik.name');

       if ($this->request->get('status') && $this->request->get('status') != 0) {
           $builder = $builder->where('appointment_lab.status', $this->request->get('status'));
       }
       if ($this->request->get('klinik_id') && $this->request->get('klinik_id') != 0) {
           $builder = $builder->where('appointment_lab.klinik_id', $this->request->get('klinik_id'));
       }
       if ($this->request->get('daterange')) {
           $getDateRange = $this->request->get('daterange');
           $dateSplit = explode(' | ', $getDateRange);
           $dateStart = date('Y-m-d 00:00:00', strtotime($dateSplit[0]));
           $dateEnd = isset($dateSplit[1]) ? date('Y-m-d 23:59:59', strtotime($dateSplit[1])) : date('Y-m-d 23:59:59', strtotime($dateSplit[0]));

           $builder = $builder->whereBetween('appointment_lab.created_at', [$dateStart, $dateEnd]);
       }

       $dataTables = $dataTables->eloquent($builder)
           ->addColumn('action', function ($query) {
               return view($this->listView['dataTable'], [
                   'query' => $query,
                   'thisRoute' => $this->route,
                   'permission' => $this->permission,
                   'masterId' => $this->masterId
               ]);
           })
           ->addColumn('time_book', function ($query) {
               return view($this->listView['timeBook'], [
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


}
