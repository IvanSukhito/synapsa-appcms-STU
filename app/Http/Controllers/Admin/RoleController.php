<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;

use Illuminate\Http\Request;

class RoleController extends _CrudController
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
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'custom' => ',orderable:false'
            ]
        ];

        parent::__construct(
            $request, 'general.role', 'role', 'Role', 'role',
            $passingData
        );

        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.role.forms';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.role.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.role.forms';

    }

    public function store()
    {
        $this->callPermission();

        $view_type = 'create';

        $getListCollectData = collectPassingData($this->passingData, $view_type);
        $validate = $this->setValidateData($getListCollectData, $view_type);
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

        $data = $this->getCollectedData($getListCollectData, $view_type, $data);

        $permission = getValidatePermissionMenu($this->request->get('permission'));

        $data['permission_data'] = json_encode($permission);
        $data['permission_route'] = json_encode(getPermissionRouteList($permission));

        $getData = $this->crud->store($data);

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route('admin.' . $this->route . '.show', $id);
        }
    }

    public function update($id)
    {
        $this->callPermission();

        $view_type = 'edit';

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route('admin.' . $this->route . '.index');
        }

        $getListCollectData = collectPassingData($this->passingData, $view_type);
        $validate = $this->setValidateData($getListCollectData, $view_type, $id);
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

        $data = $this->getCollectedData($getListCollectData, $view_type, $data, $getData);

        $permission = getValidatePermissionMenu($this->request->get('permission'));

        $data['permission_data'] = json_encode($permission);
        $data['permission_route'] = json_encode(getPermissionRouteList($permission));

        $getData = $this->crud->update($data, $id);

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_edit_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_edit_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route('admin.' . $this->route . '.show', $id);
        }
    }

}
