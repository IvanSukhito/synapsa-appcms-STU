<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\Klinik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;


class BannerPageClinicController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
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
            $request, 'general.banner_clinic', 'banner-clinic', 'V1\Sliders', 'banner-clinic',
            $passingData
        );

        $this->data['listSet']['status'] = get_list_active_inactive();
        $this->data['listSet']['type'] = get_list_sliders_type();
        $this->data['listSet']['target_menu'] = get_list_target_menu_banner();

        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.sliders.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.sliders.forms';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.sliders.forms';
    }

    public function index()
    {
        $this->callPermission();

        $adminClinicId = session()->get('admin_clinic_id');
        if(!$adminClinicId) {
            session()->flash('message', __('Tidak ada clinic yang di assign'));
            session()->flash('message_alert', 1);
            return redirect()->route('admin');
        }

        $data = $this->data;

        $data['passing'] = collectPassingData($this->passingData);

        return view($this->listView['index'], $data);
    }

    public function dataTable()
    {
        $this->callPermission();

        $adminClinicId = session()->get('admin_clinic_id');

        $dataTables = new DataTables();

        $builder = $this->model::query()->select('*')->where('klinik_id', $adminClinicId);

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
            if (in_array($list['type'], ['select', 'select2', 'multiselect2'])) {
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName) {
                    $getList = isset($this->data['listSet'][$fieldName]) ? $this->data['listSet'][$fieldName] : [];
                    return isset($getList[$query->$fieldName]) ? $getList[$query->$fieldName] : $query->$fieldName;
                });
            }
            else if (in_array($list['type'], ['image', 'image_preview'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    if ($query->{$fieldName.'_full'}) {
                        return '<img src="' . $query->{$fieldName.'_full'}. '" class="img-responsive max-image-preview"/>';
                    }
                    return '<img src="' . asset($list['path'] . $query->$fieldName) . '" class="img-responsive max-image-preview"/>';
                });
            }
            else if (in_array($list['type'], ['code'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<pre>' . json_encode(json_decode($query->$fieldName, true), JSON_PRETTY_PRINT) . '</pre>';
                });
            }
            else if (in_array($list['type'], ['texteditor'])) {
                $listRaw[] = $fieldName;
            }
        }

        return $dataTables
            ->rawColumns($listRaw)
            ->make(true);
    }

    public function store()
    {
        $this->callPermission();

        $adminClinicId = session()->get('admin_clinic_id');
        if(!$adminClinicId) {
            session()->flash('message', __('Tidak ada clinic yang di assign'));
            session()->flash('message_alert', 1);
            return redirect()->route('admin');
        }

        $viewType = 'create';

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
        $data['klinik_id'] = $adminClinicId;
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

        $adminClinicId = session()->get('admin_clinic_id');
        if(!$adminClinicId) {
            session()->flash('message', __('Tidak ada clinic yang di assign'));
            session()->flash('message_alert', 1);
            return redirect()->route('admin');
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
        $data['image'] = $adminClinicId;
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
