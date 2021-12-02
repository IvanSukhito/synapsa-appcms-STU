<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\Admin;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;


class AppointmentLabScheduleController extends _CrudController
{
    protected $setting;
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
            'total_test' => [
                'create' => 0,
                'edit' => 0,
                'list' => 0
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
            $request, 'general.appointment_lab_schedule', 'appointment-lab-schedule', 'V1\AppointmentLab', 'appointment-lab-schedule',
            $passingData
        );

        $this->setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });

        $getUsers = Users::where('status', 80)->pluck('fullname', 'id')->toArray();
        $listUsers = [];
        if($getUsers) {
            foreach($getUsers as $key => $value) {
                $listUsers[$key] = $value;
            }
        }

        $getServiceLab = isset($this->setting['service-lab']) ? json_decode($this->setting['service-lab'], true) : [];
        if (count($getServiceLab) > 0) {
            $service = Service::whereIn('id', $getServiceLab)->where('status', '=', 80)->pluck('name','id')->toArray();
        }
        else {
            $service = Service::where('status', '=', 80)->orderBy('orders', 'ASC')->pluck('name','id')->toArray();
        }


        $service_id = [0 => 'All'];
        foreach($service as $key => $val) {
            $service_id[$key] = $val;
        };

        $status = [0 => 'All'];
        foreach(get_list_appointment() as $key => $val) {
            $status[$key] = $val;
        }

        $this->data['listSet']['user_id'] =  $listUsers;
        $this->data['listSet']['service_id'] = $service_id;

        $this->data['listSet']['status'] = $status;
        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.appointment-lab-schedule.list';
    }

    public function  index(){
        $this->callPermission();

        $data = $this->data;

        return view($this->listView['index'], $data);
    }



}
