<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\Admin;
use App\Codes\Models\Role;
use App\Codes\Models\V1\Klinik;
use Illuminate\Http\Request;

class KlinikController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'name' => [
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
                'list' => 0,
            ],
            'email' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.email',
                'type' => 'email',
            ],
            'no_telp' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.clinic_phone'
            ],
            'status' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
                'list' => 0,
            ],
            'monday' => [
                'create' => 0,
                'edit' => 0,
            ],
            'tuesday' => [
                'create' => 0,
                'edit' => 0,
            ],
            'wednesday' => [
                'create' => 0,
                'edit' => 0,
            ],
            'thursday' => [
                'create' => 0,
                'edit' => 0,
            ],
            'friday' => [
                'create' => 0,
                'edit' => 0,
            ],
            'saturday' => [
                'create' => 0,
                'edit' => 0,
            ],
            'sunday' => [
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
            $request, 'general.klinik', 'klinik', 'V1\Klinik', 'klinik',
            $passingData
        );

        $this->data['listSet']['status'] = get_list_active_inactive();
        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.clinic.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.clinic.forms';


    }
    public function store()
    {
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

        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $saveData = [
            'monday' => $this->request->get('monday'),
            'tuesday' => $this->request->get('tuesday'),
            'wednesday' => $this->request->get('wednesday'),
            'thursday' => $this->request->get('thursday'),
            'friday' => $this->request->get('friday'),
            'saturday' => $this->request->get('saturday'),
            'sunday' => $this->request->get('sunday'),
        ];

        foreach($saveData as $key => $val) {
            $data[$key] = $val;
        }

        $getData = $this->crud->store($data);

        $id = $getData->id;

        $role_clinic = Role::where('name', 'Clinic')->first();

        Admin::create([
            'role_id' => $role_clinic->id,
            'klinik_id'=> $getData->id,
            'name' => $getData->name,
            'username' => strtolower(str_replace(' ', '', $getData->name)),
            'password' => bcrypt('123'),
            'status' => 80
        ]);


        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.show', $id);
        }
    }


    public function update($id)
    {
        $this->callPermission();

        $viewType = 'edit';

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getListCollectData = collectPassingData($this->passingData, $viewType);
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

        foreach ($getListCollectData as $key => $val) {
            if($val['type'] == 'image_many') {
                $getStorage = explode(',', $this->request->get($key.'_storage')) ?? [];
                $getOldData = json_decode($getData->$key, true);
                $tempData = [];
                if ($getOldData) {
                    foreach ($getOldData as $index => $value) {
                        if (in_array($index, $getStorage)) {
                            $tempData[] = $value;
                        }
                    }
                }
                if (isset($data[$key])) {
                    foreach (json_decode($data[$key], true) as $index => $value) {
                        $tempData[] = $value;
                    }
                }
                $data[$key] = json_encode($tempData);
            }
        }

        $saveData = [
            'monday' => $this->request->get('monday'),
            'tuesday' => $this->request->get('tuesday'),
            'wednesday' => $this->request->get('wednesday'),
            'thursday' => $this->request->get('thursday'),
            'friday' => $this->request->get('friday'),
            'saturday' => $this->request->get('saturday'),
            'sunday' => $this->request->get('sunday'),
        ];

        foreach($saveData as $key => $val) {
            $data[$key] = $val;
        }

        $getData = $this->crud->update($data, $id);

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_edit_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_edit_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute . '.' . $this->route . '.show', $id);
        }
    }



}
