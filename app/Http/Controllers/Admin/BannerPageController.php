<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\BannerCategory;
use App\Codes\Models\V1\Klinik;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class BannerPageController extends _CrudController
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
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
                'lang' => 'general.klinik'
            ],
            'title' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
           ],
            'image_full' => [
                'validate' => [
                    'create' => 'required',
                ],
                'type' => 'image',
                'general' => 'image',
                'list' => 0,
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
                'type' => 'datetime'
            ],
            'type' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select'
            ],
            'target_url' => [
                'list' => 0,
                'lang' => 'general.target_url',
            ],
            'target_menu' => [
                'list' => 0,
                'type' => 'select',
                'lang' => 'general.target_menu'
            ],
            'target_id' => [
                'list' => 0,
                'type' => 'number',
                'lang' => 'general.target_id'
            ],
            'orders' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'number',
                'list' => 0,
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
                'custom' => ',orderable:false'
            ]
        ];

        parent::__construct(
            $request, 'general.banner', 'banner', 'V1\Sliders', 'banner',
            $passingData
        );

        $listKlinik = [0 => 'Empty'];
        foreach (Klinik::where('status', 80)->pluck('name', 'id')->toArray() as $key => $value) {
            $listKlinik[$key] = $value;
        }

        $this->data['listSet']['status'] = get_list_active_inactive();
        $this->data['listSet']['type'] = get_list_sliders_type();
        $this->data['listSet']['target_menu'] = get_list_target_menu_banner();
        $this->data['listSet']['klinik_id'] = $listKlinik;

        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.sliders.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.sliders.forms';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.sliders.forms';
    }

    public function store()
    {
        $this->callPermission();

        $viewType = 'create';

        if($this->request->get('klinik_id') <= 0) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'klinik_id' => __('general.data_empty')
                ]);
        }

        $getListCollectData = collectPassingData($this->passingData, $viewType);

        unset($getListCollectData['image_full']);

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

        $type = intval($data['type']);

        if($type == 1) {
            $this->validate($this->request, [
                'target_url' => 'required|url'
            ]);
        }
        else if($type == 2) {
            $this->validate($this->request, [
                'target_menu' => 'required'
            ]);
        }
        else if($type == 3) {
            $this->validate($this->request, [
                'target_menu' => 'required',
                'target_id' => 'required|numeric'
            ]);
        }

        $dokument = $this->request->file('image_full');
        if ($dokument) {
            if ($dokument->getError() != 1) {

                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/banner';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }
        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $data['image'] = $dokumentImage;

        $getData = $this->crud->store($data);

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

    public function update($id)
    {
        $this->callPermission();

        $viewType = 'edit';

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        if($this->request->get('klinik_id') <= 0) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'klinik_id' => __('general.data_empty')
                ]);
        }

        $getListCollectData = collectPassingData($this->passingData, $viewType);

        unset($getListCollectData['image_full']);

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

        $type = intval($data['type']);

        if($type == 1) {
            $this->validate($this->request, [
                'target_url' => 'required|url'
            ]);
        }
        else if($type == 2) {
            $this->validate($this->request, [
                'target_menu' => 'required'
            ]);
        }
        else if($type == 3) {
            $this->validate($this->request, [
                'target_menu' => 'required',
                'target_id' => 'required|numeric'
            ]);
        }

        $dokument = $this->request->file('image_full');
        if ($dokument) {
            if ($dokument->getError() != 1) {

                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/doctor_category';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }elseif($dokument == null){

            $dokumentImage =  $getData->image;

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

        $data['image'] = $dokumentImage;
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

}
