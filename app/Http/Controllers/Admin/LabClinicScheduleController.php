<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;

use App\Codes\Logic\ExampleLogic;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Admin;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\LabService;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\LabSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class LabClinicScheduleController extends _CrudController
{
    protected $setting;
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

        $this->setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });

        $getServiceLab = isset($this->setting['service-lab']) ? json_decode($this->setting['service-lab'], true) : [];
        if (count($getServiceLab) > 0) {
            $service = Service::whereIn('id', $getServiceLab)->where('status', '=', 80)->pluck('name','id')->toArray();
        }
        else {
            $service = Service::where('status', '=', 80)->orderBy('orders', 'ASC')->pluck('name','id')->toArray();
        }


        $service_id = [];
        foreach($service as $key => $val) {
            $service_id[$key] = $val;
        };

        $klinik_id = [0 => 'Empty'];
        foreach(Klinik::where('status', 80)->pluck('name', 'id')->toArray() as $key => $val) {
            $klinik_id[$key] = $val;
        }

        $this->data['listSet']['klinik_id'] = $klinik_id;
        $this->data['listSet']['service_id'] = $service_id;
        $this->data['listSet']['day'] = get_list_day();
        $this->data['listSet']['book'] = get_list_available();
        $this->data['listSet']['weekday'] = get_list_weekday();
        $this->data['listSet']['schedule_type'] = get_list_schedule_type();
        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.lab_clinic.schedule';
        $this->listView['create2'] = env('ADMIN_TEMPLATE').'.page.lab.forms2';

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

        $listSetCarbonDay = get_list_carbon_day();

        $now = Carbon::now();

        $getListDate = LabSchedule::select('date_available')
            ->where('klinik_id', $adminClinicId)
            ->whereIn('type', [0,2])
            ->where('date_available', '!=', null)
            ->groupBy('date_available')
            ->orderBy('date_available', 'ASC')
            ->get();

        $getListDay = LabSchedule::select('weekday')
            ->where('klinik_id', $adminClinicId)
            ->where('type', 1)
            ->groupBy('weekday')
            ->orderBy('weekday', 'ASC')
            ->get();

        $getListWeekday = $this->data['listSet']['weekday'];

        $getTargetDay = $this->request->get('date') > 0 ? $this->request->get('date') : Carbon::now()->dayOfWeekIso;

        $notFound = 1;
        $findFirstDay = '';
        $temp = [];
        foreach ($getListDay as $list) {
            $temp[$list->weekday] = $getListWeekday[$list->weekday];
            if (strlen($findFirstDay) <= 0) {
                $findFirstDay = $list->weekday;
            }
            if ($getTargetDay == $list->weekday) {
                $notFound = 0;
            }
        }

        if(count($getListDate) > 0) {
            foreach($getListDate as $list) {
                $startDate = $now->startOfWeek()->format('Y-m-d');
                $endDate = $now->endOfWeek()->format('Y-m-d');
                if($list->date_available >= $startDate && $list->date_available <= $endDate) {
                    $date = Carbon::parse($list->date_available)->dayOfWeekIso;
                    $temp[$date] = $getListWeekday[$date] . ' - ' . $list->date_available;
                    if (strlen($findFirstDay) <= 0) {
                        $findFirstDay = $date;
                    }
                    if ($getTargetDay == $date) {
                        $notFound = 0;
                    }
                }
            }
        }

        $getListDay = $temp;
        $getListDay = collect($getListDay);
        $getListDay = $getListDay->sortKeys();

        if ($notFound == 1 && strlen($findFirstDay) > 0) {
            $getTargetDay = $findFirstDay;
        }

        $weekStartDate = $now->startOfWeek($listSetCarbonDay[$getTargetDay])->format('Y-m-d');

        $checkGetListSchedule = LabSchedule::where('date_available', $weekStartDate)
            ->where('klinik_id', $adminClinicId)
            ->whereIn('type', [0,2])
            ->get();

        if(count($checkGetListSchedule) > 0) {
            $getTargetDay = $weekStartDate;
        }

        if(in_array($getTargetDay, [1,2,3,4,5,6,7])) {
            $getData = LabSchedule::where('weekday', $getTargetDay)
                ->where('klinik_id', $adminClinicId)
                ->orderBy('id', 'DESC')
                ->get();

            $scheduleType = 1;
        }
        else {
            $getData = LabSchedule::where('date_available', $getTargetDay)
                ->where('klinik_id', $adminClinicId)
                ->orderBy('id', 'DESC')
                ->get();

            $scheduleType = 2;

            $getTargetDay = date('w', strtotime($getTargetDay));
        }

        if(count($getData) > 0) {
            $tempData = [];
            foreach($getData as $list) {
                $date = strlen($list->date_available) > 0 ? $list->date_available : $now->startOfWeek($listSetCarbonDay[$list->weekday])->format('Y-m-d');

                $checkAppointment = AppointmentLab::where('schedule_id', $list->schedule_id)
                    ->where('date', $date)
                    ->where('time_start', $list->time_start)
                    ->where('time_end', $list->time_end)
                    ->first();

                if($checkAppointment) {
                    $list->book = 99;
                }
                else {
                    $list->book = 80;
                }

                $tempData[] = $list;
            }
            $getData = $tempData;
        }

        $data = $this->data;

        $data['parentLabel'] = $data['thisLabel'];
        $data['thisLabel'] = __('general.lab_schedule');
        $data['listSet']['service'] = $this->data['listSet']['service_id'];
        $data['getListDay'] = $getListDay;
        $data['getTargetDay'] = $getTargetDay;
        $data['getListWeekday'] = $getListWeekday;
        $data['scheduleType'] = $scheduleType;
        $data['getData'] = $getData;

        return view($this->listView['index'], $data);
    }

    public function store(){

        $this->callPermission();

        $this->validate($this->request, [
            'schedule_type' => 'required',
        ]);

        $adminClinicId = session()->get('admin_clinic_id');

        if($this->request->get('schedule_type') == 1) {
            $data = $this->validate($this->request, [
                'service' => 'required',
                'time_start' => 'required',
                'time_end' => 'required',
                'weekday' => 'required',
            ]);

            $getWeekday = intval($data['weekday']);
            $getServiceId = intval($data['service']);
            $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
            $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

            LabSchedule::create([
                'klinik_id' => $adminClinicId,
                'service_id' => $getServiceId,
                'weekday' => $getWeekday,
                'time_start' => $getTimeStart,
                'time_end' => $getTimeEnd,
                'type' => 1,
                'book' => 80
            ]);
        }
        else {
            $data = $this->validate($this->request, [
                'service' => 'required',
                'time_start' => 'required',
                'time_end' => 'required',
                'date' => 'required',
            ]);

            $getDate = strtotime($data['date']) > 0 ? date('Y-m-d', strtotime($data['date'])) : date('Y-m-d', strtotime("+1 day"));
            $getServiceId = intval($data['service']);
            $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
            $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

            LabSchedule::create([
                'klinik_id' => $adminClinicId,
                'service_id' => $getServiceId,
                'date_available' => $getDate,
                'time_start' => $getTimeStart,
                'time_end' => $getTimeEnd,
                'type' => 2,
                'book' => 80
            ]);
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

    public function update($id){
        $this->callPermission();

        $adminClinicId = session()->get('admin_clinic_id');

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();
        if (!$getAdmin) {
            return redirect()->route($this->rootRoute . '.' . $this->route . '.index');
        }

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute . '.' . $this->route . '.index');
        }

        if($this->request->get('schedule_type') == 1) {
            $data = $this->validate($this->request, [
                'service' => 'required',
                'time_start' => 'required',
                'time_end' => 'required',
                'weekday' => 'required',
            ]);

            $getWeekday = intval($data['weekday']);
            $getServiceId = intval($data['service']);
            $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
            $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

            $getData->klinik_id = $adminClinicId;
            $getData->service_id = $getServiceId;
            $getData->weekday = $getWeekday;
            $getData->time_start = $getTimeStart;
            $getData->time_end = $getTimeEnd;
        }
        else {
            $data = $this->validate($this->request, [
                'service' => 'required',
                'time_start' => 'required',
                'time_end' => 'required',
                'date' => 'required',
            ]);

            $getDate = strtotime($data['date']) > 0 ? date('Y-m-d', strtotime($data['date'])) : date('Y-m-d', strtotime("+1 day"));
            $getServiceId = intval($data['service']);
            $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
            $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

            $getData->klinik_id = $adminClinicId;
            $getData->service_id = $getServiceId;
            $getData->date_available = $getDate;
            $getData->time_start = $getTimeStart;
            $getData->time_end = $getTimeEnd;
        }

        $getData->save();

        if ($this->request->ajax()) {
            return response()->json(['result' => 1, 'message' => __('general.success_edit_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_edit_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute . '.' . $this->route . '.index');
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
            $getLogic = new ExampleLogic();
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

        //A-F
        //A = Nomor
        //B = Service
        //C = Date Available
        //D = Weekday
        //E = Time Start
        //F = Time End

        //Start From Row 7

        $getFile = $this->request->file('import_lab_schedule');

        if($getFile) {

//            $destinationPath = 'synapsaapps/lab-schedule/example_import';
//
//            $getUrl = Storage::put($destinationPath, $getFile);
//
//            die(env('OSS_URL') . '/' . $getUrl);

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
                        try {
                            if ($key > 6) {
                                $getService = $spreadsheet->getCell("B" . $key)->getValue();
                                $getDateAvailable = $spreadsheet->getCell("C" . $key)->getValue();
                                $getWeekday = strtolower($spreadsheet->getCell("D" . $key)->getValue());
                                $getTimeStart = $spreadsheet->getCell("E" . $key)->getValue();
                                $getTimeEnd = $spreadsheet->getCell("F" . $key)->getValue();

                                $weekdayID = [
                                    1 => 'senin',
                                    2 => 'selasa',
                                    3 => 'rabu',
                                    4 => 'kamis',
                                    5 => 'jumat',
                                    6 => 'sabtu',
                                    7 => 'minggu',
                                ];

                                $weekdayEN = [
                                    1 => 'monday',
                                    2 => 'tuesday',
                                    3 => 'wednesday',
                                    4 => 'thursday',
                                    5 => 'friday',
                                    6 => 'saturday',
                                    7 => 'sunday',
                                ];

                                $getWeekdays = array_search($getWeekday, $weekdayID);
                                if (!$getWeekdays) {
                                    $getWeekdays = array_search($getWeekday, $weekdayEN);
                                }

                                if ($getDateAvailable != null) {
                                    $getDateAvailable = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($getDateAvailable)->format('Y-m-d');
                                }

                                $setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
                                    return Settings::pluck('value', 'key')->toArray();
                                });

                                $getLabService = Service::whereIn('id', json_decode($setting['service-lab'], true))->get();

                                $service_id = [];
                                foreach ($getLabService as $val) {
                                    $service_id[$val->id] = $val->name;
                                }

                                $adminClinicId = session()->get('admin_clinic_id');

                                $getService = intval($getService);

                                if (array_key_exists($getService, $service_id)) {
                                    $saveData = [
                                        'klinik_id' => $adminClinicId,
                                        'service_id' => $getService,
                                        'date_available' => strlen($getDateAvailable) > 0 ? $getDateAvailable : null,
                                        'weekday' => strlen($getDateAvailable) > 0 ? 0 : $getWeekdays,
                                        'time_start' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($getTimeStart)->format('H:i:s'),
                                        'time_end' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($getTimeEnd)->format('H:i:s'),
                                        'book' => 80,
                                        'type' => strlen($getDateAvailable) > 0 ? 2 : 1,
                                    ];

                                    $checkSchedule = LabSchedule::where('klinik_id', $adminClinicId);
                                    foreach ($saveData as $column => $value) {
                                        if ($column != 'klinik_id') {
                                            $checkSchedule->where($column, $value);
                                        }
                                    }
                                    $checkSchedule = $checkSchedule->first();

                                    if (!$checkSchedule) {
                                        $labSchedule = LabSchedule::create($saveData);
                                    }
                                }
                            }
                        }
                        catch (\Exception $e) {
                            isset($labSchedule) ? $labSchedule->delete() : '';
                            continue;
                        }
                    }
                }
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
