<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\Admin;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\LabService;
use App\Codes\Models\V1\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class LabClinicController extends _CrudController
{
    protected $setting;
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'Nomor Transaksi'
            ],
            'parent_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
                'lang' => 'Layanan',
            ],
            'name' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ]
            ],
            'image_full' => [
                'validate' => [
                    'create' => 'required',
                ],
                'type' => 'image',
                'custom' => ', name: "image"'
            ],
            'desc_lab' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'texteditor',
                'list' => 0,
            ],
            'desc_benefit' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'texteditor',
                'list' => 0,
            ],
            'desc_preparation' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'texteditor',
                'list' => 0,
            ],
            'recommended_for' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'multiselect2',
                'list' => 0,
            ],
            'service_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => ''
                ],
                'type' => 'multiselect2',
                'show' => 0,
                'list' => 0,
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'Aksi',
            ]
        ];

        parent::__construct(
            $request, 'general.appointment_lab_clinic', 'lab-appointment', 'V1\Lab', 'lab-appointment',
            $passingData
        );

        $this->setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });

        $getServiceLab = isset($this->setting['service-lab']) ? json_decode($this->setting['service-lab'], true) : [];

        //dd($getServiceLab);
        if (count($getServiceLab) > 0) {
            $service = Service::whereIn('id', $getServiceLab)->where('status', '=', 80)->pluck('name','id')->toArray();
        }
        else {
            $service = Service::where('status', '=', 80)->orderBy('orders', 'ASC')->pluck('name','id')->toArray();
        }

        $service_id = [];
        foreach($service as $key => $val) {
            $service_id[$key] = $val;
        }


        $getParent = Lab::get();
        $listParent = [0 => 'Tidak memiliki Parent'];
        if($getParent) {
            foreach($getParent as $list) {
                $listParent[$list->id] = $list->name;
            }
        }

        $this->data['listSet']['parent_id'] = $listParent;
        $this->data['listSet']['service_id'] = $service_id;

        $this->data['listSet']['recommended_for'] = get_list_recommended_for();
        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.lab_clinic.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.lab_clinic.forms_edit';
    }

    public function store(){
        $this->callPermission();

        $viewType = 'create';

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();

        if(!$getAdmin){
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $this->validate($this->request, [
            'service_id' => 'required',
            'price' => 'required'
        ]);

        $getListCollectData = collectPassingData($this->passingData, $viewType);

        unset($getListCollectData['service_id']);

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

        unset($data['image_full']);
        unset($getListCollectData['image_full']);


        $dokument = $this->request->file('image_full');
        if ($dokument) {
            if ($dokument->getError() != 1) {

                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/lab';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }


        $recommend = $data['recommended_for'];

        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $data['image'] = $dokumentImage;
        $data['klinik_id'] = $getAdmin->klinik_id;
        $data['recommended_for'] = json_encode($recommend);

        $getData = $this->crud->store($data);

        $serviceId = $this->request->get('service_id');
        $price = clear_money_format($this->request->get('price'));

        foreach($serviceId as $key => $list){

            LabService::create([
                'lab_id' => $getData->id,
                'service_id' => $list,
                'price' => $price[$key] != null ? $price[$key] : 0
            ]);
        }

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }
    }

    public function update($id){
        $this->callPermission();

        $viewType = 'edit';

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();

        if(!$getAdmin){
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getListCollectData = collectPassingData($this->passingData, $viewType);

        unset($getListCollectData['service_id']);

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

        unset($getListCollectData['image_full']);
        unset($data['image_full']);

        $dokument = $this->request->file('image_full');
        $dokumentImage = null;
        if ($dokument) {
            if ($dokument->getError() != 1) {

                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/lab';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }else{
            $dokumentImage = $getData->image;
        }

        $recommend = $data['recommended_for'];

        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $data['image'] = $dokumentImage;
        $data['klinik_id'] = $getAdmin->klinik_id;
        $data['recommended_for'] = json_encode($recommend);

        $getData = $this->crud->update($data, $id);

        $serviceId = $this->request->get('service_id');
        $price = clear_money_format($this->request->get('price'));

        if($serviceId){
            $saveDataTemp = [];
            $lab = Lab::where('id', $id)->first();
            foreach($serviceId as $key => $list){
                $prices = $price[$key] != null ? $price[$key] : 0;

                $saveDataTemp[$list] = [
                    'price' => $prices
                ];
            }
            $lab->getService()->sync($saveDataTemp);
        }

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

    public function show($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getData->recommended_for = json_decode($getData->recommended_for);

        $getLabService = LabService::where('lab_id',$id)->get();

        $data = $this->data;

        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;
        $data['labService'] = $getLabService;

        return view($this->listView[$data['viewType']], $data);
    }

    public function edit($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getData->recommended_for = json_decode($getData->recommended_for);

        $getLabService = LabService::where('lab_id',$id)->get();

        $data = $this->data;

        $data['viewType'] = 'edit';
        $data['formsTitle'] = __('general.title_edit', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;
        $data['labService'] = $getLabService;

        return view($this->listView[$data['viewType']], $data);
    }

    public function dataTable()
    {
        $this->callPermission();

        $dataTables = new DataTables();

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();

        if(!$getAdmin){
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $builder = $this->model::query()->select('*')->where('klinik_id', $getAdmin->klinik_id);

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
}
