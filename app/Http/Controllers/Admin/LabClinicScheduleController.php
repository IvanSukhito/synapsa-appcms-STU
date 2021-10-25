<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;

use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Admin;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\LabSchedule;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LabClinicScheduleController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'lab_id' => [

                'type' => 'select2',
                'create' => 0,
                'edit' => 0,
                'list' => 0,
            ],
            'service_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
            ],
            'date_available' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'datepicker',
            ],
            'time_start' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'time',
            ],
            'time_end' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'time',
            ],
            'book' => [
                'validate' => [
                    'create' => 0,
                    'edit' => 0
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
            $request, 'general.lab_clinic_schedule', 'lab-clinic-schedule', 'V1\LabSchedule', 'lab-clinic-schedule',
            $passingData
        );


        $getService = Service::where('status', 80)->pluck('name', 'id')->toArray();
        if($getService) {
            foreach($getService as $key => $value) {
                $listService[$key] = $value;
            }
        }



        $this->data['listSet']['service_id'] = $listService;
        $this->data['listSet']['day'] = get_list_day();
        $this->data['listSet']['book'] = get_list_availabe();
        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.lab.schedule';
        $this->listView['create2'] = env('ADMIN_TEMPLATE').'.page.lab.forms2';

    }

    public function index()
    {
        $this->callPermission();


        $getTargetDate = strtotime($this->request->get('date')) > 0 ? date('Y-m-d', strtotime($this->request->get('date'))) : date('Y-m-d');

        $getListDate = LabSchedule::select('date_available')
            ->where('date_available', '>=', date('Y-m-d'))
            ->groupBy('date_available')
            ->orderBy('date_available', 'DESC')
            ->get();


        $notFound = 1;
        $findFirstDate = '';
        $temp = [];
        foreach ($getListDate as $list) {
            $temp[$list->date_available] = date('d-F-Y', strtotime($list->date_available));
            if (strlen($findFirstDate) <= 0) {
                $findFirstDate = $list->date_available;
            }
            if ($getTargetDate == $list->date_available) {
                $notFound = 0;
            }
        }
        $getListDate = $temp;

        if ($notFound == 1 && strlen($findFirstDate) > 0) {
            $getTargetDate = $findFirstDate;
        }

        $getData = LabSchedule::where('date_available', $getTargetDate)->orderBy('id','DESC')->get();

        $data = $this->data;
        $data['parentLabel'] = $data['thisLabel'];
        $data['thisLabel'] = __('general.lab_schedule');
        $data['listSet']['service'] = $this->data['listSet']['service_id'];
        $data['getListDate'] = $getListDate;
        $data['getTargetDate'] = $getTargetDate;
        $data['getData'] = $getData;

        return view($this->listView['index'], $data);
    }

    public function store(){

        $this->callPermission();

        $viewType = 'create';

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();

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

        $getServiceId = intval($data['service_id']);
        $getDate = strtotime($data['date_available']) > 0 ? date('Y-m-d', strtotime($data['date_available'])) : date('Y-m-d', strtotime("+1 day"));
        $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
        $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $data['lab_id'] = 0;
        $data['date_available'] = $getDate;
        $data['klinik_id'] = $getAdmin->klinik_id;
        $data['time_start'] = $getTimeStart;
        $data['time_end'] = $getTimeEnd;
        $data['book'] = 80;

        $getData = $this->crud->store($data);

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

         $getData = Users::where('id', $adminId)->first();

         if (!$getData) {
             return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
         }

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

        $getServiceId = intval($data['service_id']);
        $getDate = strtotime($data['date_available']) > 0 ? date('Y-m-d', strtotime($data['date_available'])) : date('Y-m-d', strtotime("+1 day"));
        $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
        $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

        $data = $this->getCollectedData($getListCollectData, $viewType, $data, $getData);

        $data['lab_id'] = 0;
        $data['klinik_id'] = $getAdmin->klinik_id;
        $data['date_available'] = $getDate;
        $data['time_start'] = $getTimeStart;
        $data['time_end'] = $getTimeEnd;
        $data['book'] = 80;

        $getData = $this->crud->update($data, $id);

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_edit_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_edit_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }
    }

    public function create2(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getData = Admin::where('id', $adminId)->first();
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        if($this->request->get('download_example_import')) {
            $getLogic = new SynapsaLogic();
            $getLogic->downloadExampleImportLabSchedule();
        }

        $data = $this->data;

        $data['thisLabel'] = __('general.lab_schedule');
        $data['viewType'] = 'create';
        $data['formsTitle'] = __('general.title_create', ['field' => __('general.lab_schedule')]);
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
            'import_lab_schedule' => 'required',
        ]);

        //A-N
        //A = Nomor
        //B = Service
        //C = Date
        //D = Time Start
        //E = Time End

        //Start From Row 6

        $getFile = $this->request->file('import_lab_schedule');

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
                                $getService = $spreadsheet->getCell("B" . $key)->getValue();
                                $getDate = $spreadsheet->getCell("C" . $key)->getValue();
                                $getTimeStart = $spreadsheet->getCell("D" . $key)->getValue();
                                $getTimeEnd = $spreadsheet->getCell("E" . $key)->getValue();

                                $klinik_id = session()->get('admin_clinic_id');

                                if($klinik_id){
                                    $saveData = [
                                        'klinik_id' => $klinik_id,
                                        'lab_id' => 0,
                                        'service_id' => $getService,
                                        'date_available' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($getDate)->format('Y-m-d'),
                                        'time_start' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($getTimeStart)->format('H:i:s'),
                                        'time_end' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($getTimeEnd)->format('H:i:s'),
                                        'book' => 80,
                                    ];

                                    $labSchedule = LabSchedule::create($saveData);

                                }
                            }
                        }
                    }
                }
            }
            catch(\Exception $e) {
               // $labSchedule->delete();

                session()->flash('message', __('general.failed_import_lab_schedule'));
                session()->flash('message_alert', 1);
                return redirect()->route($this->rootRoute.'.' . $this->route . '.create2');
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
