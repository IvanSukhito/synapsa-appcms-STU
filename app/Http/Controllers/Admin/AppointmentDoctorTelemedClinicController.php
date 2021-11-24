<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\Admin;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;


class AppointmentDoctorTelemedClinicController extends _CrudController
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
            'doctor_name' => [
                'create' => 0,
                'edit' => 0,
            ],
            'type_appointment' => [
                'create' => 0,
                'edit' => 0,
                'list' => 0,
            ],
            'diagnosis' => [
                'create' => 0,
                'edit' => 0,
                'list' => 0,
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
            $request, 'general.doctor_clinic_telemed', 'doctor-clinic-telemed', 'V1\AppointmentDoctor', 'doctor-clinic-telemed',
            $passingData
        );


        $service_id = [];
        foreach(Service::where('status', 80)->pluck('name', 'id')->toArray() as $key => $val) {
            $service_id[$key] = $val;
        };

        $status = [0 => 'All'];
        foreach(get_list_appointment() as $key => $val) {
            $status[$key] = $val;
        }


        $this->data['listSet']['service_id'] = $service_id;
        $this->data['listSet']['status'] = $status;
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.appointment-doctor-clinic.doctor-clinic-telemed.forms';


    }

    public function show($id)
    {
        $this->callPermission();

        $adminClinicId = session()->get('admin_clinic_id');

        if (!$adminClinicId) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getData = $this->crud->show($id, [
            'id' => $id,
            'klinik_id' => $adminClinicId,
        ]);

        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $getAppointmentDoctorDetail = AppointmentDoctorProduct::selectRaw('appointment_doctor_product.*, doctor_prescription')
                                        ->leftJoin('appointment_doctor','appointment_doctor.id','=','appointment_doctor_product.appointment_doctor_id')
                                        ->where('appointment_doctor_product.appointment_doctor_id', $id)
                                        ->get();

        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;
        $data['doctorProduct'] = $getAppointmentDoctorDetail;
        $data['listSetTypeDosis'] = get_list_type_dosis();

        return view($this->listView[$data['viewType']], $data);
    }

    public function dataTable()
    {
        $this->callPermission();
        //$userId = session()->get('admin_id');
        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();

        $dataTables = new DataTables();

        $builder = $this->model::query()->selectRaw('appointment_doctor.*')
            ->where('appointment_doctor.klinik_id', $getAdmin->klinik_id)
            ->where('type_appointment', 'Telemed');

        if ($this->request->get('status') && $this->request->get('status') != 0) {
            $builder = $builder->where('appointment_lab.status', $this->request->get('status'));
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
