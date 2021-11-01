<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\DoctorService;
use Illuminate\Http\Request;
use App\Codes\Models\V1\Payment;
use Illuminate\Support\Facades\Storage;

class ShippingController extends _CrudController
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
            'price' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'icon_full' => [
                'validate' => [
                    'create' => 'required',
                ],
                'lang' => 'general.icon_img',
                'type' => 'image',
            ],
            'orders' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'number',
            ],
            'settings' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'textarea',
                'list' => 0,
                'edit' => 0,
                'create' => 0,
                'show' => 0,
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
            $request, 'general.shipping', 'shipping', 'V1\Shipping', 'shipping',
            $passingData
        );

        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.shipping.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.shipping.forms';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.shipping.forms';

        $this->data['listSet']['status'] = get_list_active_inactive();
        $this->data['listSet']['type_payment'] = get_list_type_payment();
        $this->data['listSet']['service'] = get_list_service_payment();

    }

    public function store()
    {
        $this->callPermission();

        $viewType = 'create';

        $getListCollectData = collectPassingData($this->passingData, $viewType);

        unset($getListCollectData['icon_full']);

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

        $desc = $this->request->get('desc');
        $title = $this->request->get('title');

        $settings = [];
        $settings[]  = [
            'title' => $title,
            'desc' => $desc
        ];

        $dokument = $this->request->file('icon_full');
        if ($dokument) {
            if ($dokument->getError() != 1) {
                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/shipping';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }

        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $data['icon'] = $dokumentImage;
        $data['settings'] = json_encode($settings);

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

    public function edit($id){
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $getSettings = json_decode($getData->settings, true);

        if($getSettings) {
            $temp = [];
            foreach ($getSettings as $index => $listSettings) {
                $temp = $listSettings;
            }

            $listSettings = $temp;
        }
        else {
            $title = [];
            $desc = [];
            $listSettings = [
                $title[] = 'title' => [''],
                $desc[] = 'desc' => [''],
            ];
        }

        $data['thisLabel'] = __('general.product');
        $data['viewType'] = 'edit';
        $data['formsTitle'] = __('general.title_edit', ['field' => __('general.product') . ' ' . $getData->name]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['listSettings'] = $listSettings;
        $data['data'] = $getData;

        return view($this->listView[$data['viewType']], $data);
    }

    public function update($id){
        $this->callPermission();

        $viewType = 'edit';

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getListCollectData = collectPassingData($this->passingData, $viewType);

        unset($getListCollectData['icon_full']);

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

//        $desc = $this->request->get('desc');
//        $title = $this->request->get('title');
//
//        $settings = [];
//        $settings[]  = [
//            'title' => $title,
//            'desc' => $desc
//        ];

        $dokument = $this->request->file('icon_full');
        if ($dokument) {
            if ($dokument->getError() != 1) {
                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/shipping';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }
        else {
            $dokumentImage = $getData->icon_img;
        }

        $data = $this->getCollectedData($getListCollectData, $viewType, $data, $getData);

        $data['icon'] = $dokumentImage;
        //$data['settings'] = json_encode($settings);

        $getData = $this->crud->update($data, $id);

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

    public function show($id){
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

//        $getSettings = json_decode($getData->settings, true);
//
//        if($getSettings) {
//            $temp = [];
//            foreach ($getSettings as $index => $listSettings) {
//                $temp = $listSettings;
//            }
//
//            $listSettings = $temp;
//        }
//        else {
//            $title = [];
//            $desc = [];
//            $listSettings = [
//                $title[] = 'title' => [''],
//                $desc[] = 'desc' => [''],
//            ];
//        }

        $data['thisLabel'] = __('general.product');
        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => __('general.product') . ' ' . $getData->name]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        //$data['listSettings'] = $listSettings;
        $data['data'] = $getData;

        return view($this->listView[$data['viewType']], $data);
    }

}
