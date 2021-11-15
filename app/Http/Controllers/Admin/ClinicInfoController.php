<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\Admin;
use Illuminate\Http\Request;

class ClinicInfoController extends _CrudController
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
                ],
                'lang' => 'general.clinic',
            ],

            'address' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'no_telp' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.phone'
            ],
            'email' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.email'
            ],
            'monday' => [
                'edit' => 0,
            ],
            'tuesday' => [
                'edit' => 0,
            ],
            'wednesday' => [
                'edit' => 0,
            ],
            'thursday' => [
                'edit' => 0,
            ],
            'friday' => [
                'edit' => 0,
            ],
            'saturday' => [
                'edit' => 0,
            ],
            'sunday' => [
                'edit' => 0,
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'custom' => ',orderable:false,searchable:false'
            ]
        ];

        parent::__construct(
            $request, 'general.clinic_info', 'clinic_info', 'V1\Klinik', 'clinic_info',
            $passingData
        );


        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.clinic_info.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.clinic_info.forms';

    }

    public function index()
    {
        $adminClinicId = session()->get('admin_clinic_id');


        //dd($adminClinicId);
        $getData = $this->crud->show($adminClinicId);
        if (!$getData) {
            return redirect()->route('admin');
        }

        return redirect()->route($this->rootRoute.'.' . $this->route . '.show', $adminClinicId);
    }

    public function edit($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $data['viewType'] = 'edit';
        $data['formsTitle'] = __('general.title_edit', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;

        return view($this->listView[$data['viewType']], $data);
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
            return redirect()->route($this->rootRoute.'.' . $this->route . '.show', $id);
        }
    }

    public function show($id)
    {
        $this->callPermission();

        $viewType = 'show';

        $getData = $this->crud->show($id);

        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;


        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;

        return view($this->listView[$data['viewType']], $data);
    }

}
