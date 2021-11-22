<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Admin;
use App\Codes\Models\Role;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\ProductCategory;
use App\Codes\Models\V1\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'logo_full' => [
                'validate' => [
                    'create' => 'required',
                ],
                'lang' => 'general.logo',
                'type' => 'image',
                'list' => 0,
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
        $this->listView['create2'] = env('ADMIN_TEMPLATE').'.page.clinic.forms2';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.clinic.forms';
        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.clinic.list';


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

        unset($getListCollectData['logo_full']);
        unset($data['logo_full']);

        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $logoImage = '';
        $logo = $this->request->file('logo_full');
        if ($logo) {
            if ($logo->getError() != 1) {
                $getFileName = $logo->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/klinik';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {
                    $logoImage = Storage::putFile($destinationPath, $logo);
                }
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
            'logo' => $logoImage,
        ];

        foreach($saveData as $key => $val) {
            $data[$key] = $val;
        }

        $getData = $this->crud->store($data);

        $id = $getData->id;

        $role_clinic = Role::where('permission_data', 'LIKE', '%' . json_encode('role_clinic') . '%')->first();

        Admin::create([
            'role_id' => $role_clinic->id,
            'klinik_id'=> $getData->id,
            'name' => $getData->name,
            'username' => strtolower(str_replace(' ', '', $getData->name)),
            'password' => bcrypt(strtolower(str_replace(' ', '', $getData->name))),
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

        unset($getListCollectData['logo_full']);
        unset($data['logo_full']);

        $data = $this->getCollectedData($getListCollectData, $viewType, $data, $getData);

        $logoImage = $getData->logo;
        $logo = $this->request->file('logo_full');
        if ($logo) {
            if ($logo->getError() != 1) {
                $getFileName = $logo->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/klinik';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {
                    $logoImage = Storage::putFile($destinationPath, $logo);
                }
            }
        }

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
            'logo' => $logoImage
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

    public function create2(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getAdmin = Admin::where('id', $adminId)->first();
        if (!$getAdmin) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        if($this->request->get('download_example_import')) {
            $getLogic = new SynapsaLogic();
            $getLogic->downloadExampleImportClinic();
        }

        $data = $this->data;

        $getData = $this->data;

        $data['thisLabel'] = __('general.clinic');
        $data['viewType'] = 'create';
        $data['formsTitle'] = __('general.title_create', ['field' => __('general.clinic')]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;

        return view($this->listView['create2'], $data);
    }

    public function store2(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getAdmin = Admin::where('id', $adminId)->first();
        if (!$getAdmin) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $this->validate($this->request, [
            'import_clinic' => 'required',
        ]);

        $getFile = $this->request->file('import_clinic');

        if($getFile) {

            try {
                $getFileName = $getFile->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                if (in_array(strtolower($ext), ['xlsx', 'xls'])) {
                    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($getFile);
                    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                    $data = $reader->load($getFile);

                    if ($data) {
                        $spreadsheet = $data->getActiveSheet();
                        foreach ($spreadsheet->getRowIterator() as $key => $row) {

                            if($key >= 6) {
                                $getClinicName = $spreadsheet->getCell("B" . $key)->getValue();
                                $getClinicAddress = $spreadsheet->getCell("C" . $key)->getValue();
                                $getClinicPhone = $spreadsheet->getCell("D" . $key)->getValue();
                                $getClinicEmail = $spreadsheet->getCell("E" . $key)->getValue();
                                $getScheduleMonday = $spreadsheet->getCell("F" . $key)->getValue();
                                $getScheduleTuesday = $spreadsheet->getCell("G" . $key)->getValue();
                                $getScheduleWednesday =  $spreadsheet->getCell("H" . $key)->getValue();
                                $getScheduleThursday = $spreadsheet->getCell("I" . $key)->getValue();
                                $getScheduleFriday = $spreadsheet->getCell("J" . $key)->getValue();
                                $getScheduleSaturday = $spreadsheet->getCell("K" . $key)->getValue();
                                $getScheduleSunday = $spreadsheet->getCell("L" . $key)->getValue();

                                //dd($getClinicEmail);

                                $saveData = [
                                    'name' => $getClinicName,
                                    'address' => $getClinicAddress,
                                    'no_telp' => $getClinicPhone,
                                    'email' => $getClinicEmail,
                                    'monday' => $getScheduleMonday,
                                    'tuesday' => $getScheduleTuesday,
                                    'wednesday' => $getScheduleWednesday,
                                    'thursday' => $getScheduleThursday,
                                    'friday' => $getScheduleFriday,
                                    'saturday' => $getScheduleSaturday,
                                    'sunday' => $getScheduleSunday,
                                    'status' => 80,
                                ];

                                $role_clinic = Role::where('name', 'Clinic')->first();

                                if(strlen($getClinicName) > 0) {
                                    $clinic = Klinik::create($saveData);
                                }
                                if($clinic){

                                    $saveAdmin = [
                                        'role_id' => $role_clinic->id,
                                        'klinik_id'=> $clinic->id,
                                        'name' => $clinic->name,
                                        'username' => strtolower(str_replace(' ', '', $clinic->name)),
                                        'password' => bcrypt(strtolower(str_replace(' ', '', $clinic->name))),
                                        'status' => 80
                                    ];

                                    Admin::create($saveAdmin);
                                }
                            }
                        }
                    }
                }
            }
            catch(\Exception $e) {
                $clinic->delete();

                session()->flash('message', __('general.failed_import_clinic'));
                session()->flash('message_alert', 1);
                return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
            }
        }

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }
    }


}
