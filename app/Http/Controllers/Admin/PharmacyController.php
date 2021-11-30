<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\Admin;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;


class PharmacyController extends _CrudController
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
                'custom' => ',name:"appointment_doctor.patient_name"'
            ],
            'doctor_name' => [
                'create' => 0,
                'edit' => 0,
                'custom' => ',name:"appointment_doctor.doctor_name"'
            ],
            'product_name' => [
                'create' => 0,
                'edit' => 0,
            ],
            'product_qty_checkout' => [
                'create' => 0,
                'edit' => 0,
            ],
            'dose' => [
                'create' => 0,
                'edit' => 0,
            ],
            'type_dose' => [
                'create' => 0,
                'edit' => 0,
                'type' => 'select',
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
            $request, 'general.pharmacy', 'pharmacy', 'V1\AppointmentDoctorProduct', 'pharmacy',
            $passingData
        );

        $typeDose = [0 => 'Empty'];
        foreach(get_list_type_dose() as $key => $val) {
            $typeDose[$key] = $val;
        }

        $status = [0 => 'Empty'];
        foreach(get_list_appointment() as $key => $val) {
            $status[$key] = $val;
        }


        $this->data['listSet']['type_dose'] = $typeDose;
        $this->data['listSet']['status'] = $status;
    }

    public function dataTable()
    {
        $this->callPermission();
        //$userId = session()->get('admin_id');

        $dataTables = new DataTables();

        $builder = $this->model::query()->selectRaw('appointment_doctor_product.*, appointment_doctor.doctor_name, appointment_doctor.patient_name')
            ->leftJoin('appointment_doctor','appointment_doctor.id','=','appointment_doctor_product.appointment_doctor_id');

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
