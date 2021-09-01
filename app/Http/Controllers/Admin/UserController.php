<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends _CrudController
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
            ],
            'email' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'password' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'custom' => ',orderable:false'
            ]
        ];

        parent::__construct(
            $request, 'general.user', 'user', 'User', 'user',
            $passingData
        );

        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.user.forms';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.user.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.user.forms';

    }

    public function password($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $data['viewType'] = 'edit';
        $data['formsTitle'] = __('general.title_edit', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingPassword, $data['viewType']);
        $data['data'] = $getData;

        return view($this->listView['password'], $data);
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

    public function updatePassword($id)
    {
        $this->callPermission();

        $viewType = 'edit';

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getListCollectData = collectPassingData($this->passingPassword, $viewType);
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

        unset($data['password_confirmation']);

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

    public function destroy($id)
    {
        $this->callPermission();

        if (session()->get('admin_id') == $id) {
            session()->flash('message', __('general.error_cannot_own_account'));
            session()->flash('message_alert', 1);
            return redirect()->route('admin.' . $this->route . '.index');
        }
        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route('admin.' . $this->route . '.index');
        }

        foreach ($this->passingData as $fieldName => $fieldValue) {
            if (in_array($fieldValue['type'], ['image', 'video', 'file'])) {
                $destinationPath = $fieldValue['path'];
                if (strlen($getData->$fieldName) > 0 && is_file($destinationPath.$getData->$fieldName)) {
                    unlink($destinationPath.$getData->$fieldName);
                }
            }
        }

        $this->crud->destroy($id);

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_delete_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_delete_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route('admin.' . $this->route . '.index');
        }
    }
}
