<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\AppointmentNurse;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;


class AppointmentNurseController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
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
            ],
            'date' => [
                'create' => 0,
                'edit' => 0,
                'list' => 0,
            ],
            'shift_qty' => [
                'create' => 0,
                'edit' => 0,
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
            $request, 'general.appointment_nurse', 'appointment-nurse', 'V1\AppointmentNurse', 'appointment-nurse',
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
        };
        $status = [0 => 'All'];
        foreach(get_list_appointment() as $key => $val) {
            $status[$key] = $val;
        }

        $this->data['listSet']['user_id'] =  $listUsers;
        $this->data['listSet']['service_id'] = $service_id;

        $this->data['listSet']['status'] = $status;
        $this->listView['dataTable'] = env('ADMIN_TEMPLATE').'.page.appointment-nurse.list_button';
        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.appointment-nurse.list';


    }

    public function index()
    {
        $this->callPermission();

        $data = $this->data;

        $data['passing'] = collectPassingData($this->passingData);

        return view($this->listView['index'], $data);

    }

    public function approve($id){

        $this->callPermission();


        $getData = AppointmentNurse::where('id', $id)->first();

        if(!$getData){
            session()->flash('message', __('general.data_not_found'));
            session()->flash('message_alert', 1);
            return redirect()->route('admin.' . $this->route . '.index');
        }

        $getData->status = 4;
        $getData->save();



        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add')]);
        }
        else {
            session()->flash('message', __('general.success_approve_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route('admin.' . $this->route . '.index');
        }
    }

    public function reject($id){

        $this->callPermission();


        $getData = AppointmentNurse::where('id', $id)->first();

        if(!$getData){
            session()->flash('message', __('general.data_not_found'));
            session()->flash('message_alert', 1);
            return redirect()->route('admin.' . $this->route . '.index');
        }

        $getData->status = 90;
        $getData->save();

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_reject')]);
        }
        else {
            session()->flash('message', __('general.success_reject_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route('admin.' . $this->route . '.index');
        }
    }

    public function dataTable()
   {
       $this->callPermission();
       //$userId = session()->get('admin_id');
       $dataTables = new DataTables();
       $builder = $this->model::query()->selectRaw('appointment_nurse.id, users.fullname as user, patient_name, patient_email, type_appointment, shift_qty, date, appointment_nurse.status, appointment_nurse.created_at')
           ->leftJoin('users','users.id', '=', 'appointment_nurse.user_id');

       if ($this->request->get('status') && $this->request->get('status') != 0) {
           $builder = $builder->where('appointment_nurse.status', $this->request->get('status'));
       }
       if ($this->request->get('daterange')) {
           $getDateRange = $this->request->get('daterange');
           $dateSplit = explode(' | ', $getDateRange);
           $dateStart = date('Y-m-d 00:00:00', strtotime($dateSplit[0]));
           $dateEnd = isset($dateSplit[1]) ? date('Y-m-d 23:59:59', strtotime($dateSplit[1])) : date('Y-m-d 23:59:59', strtotime($dateSplit[0]));

           $builder = $builder->whereBetween('appointment_nurse.created_at', [$dateStart, $dateEnd]);
       }

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


}
