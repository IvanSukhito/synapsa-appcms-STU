<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\ProductCategory;
use Illuminate\Http\Request;

class DoctorScheduleController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'doctor_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
            ],
            'day' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
            ],
            'time_start' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'datetime',
            ],
            'time_end' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'datetime',
            ],
            'book' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'Aksi',
            ]
        ];

        parent::__construct(
            $request, 'general.doctor_schedule', 'doctor-schedule', 'V1\DoctorSchedule', 'doctor_schedule',
            $passingData
        );


        $this->data['listSet']['day'] = get_list_day();
        $this->data['listSet']['book'] = get_list_availabe();
    }
}
