<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Admin;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\DoctorService;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\Users;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;
use App\Codes\Models\V1\Service;
use Illuminate\Http\Request;

class DoctorClinicController extends _CrudController
{
    protected $limit;

    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'user_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.name',
                'type' => 'select2',
            ],
            'doctor_category_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.doctor-category',
                'type' => 'select2',
            ],
            'formal_edu' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'texteditor',
                'list' => 0,
            ],
            'nonformal_edu' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'texteditor',
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
            $request, 'general.doctor_clinic', 'doctor_clinic', 'V1\Doctor', 'doctor_clinic',
            $passingData
        );

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();

        $getUsers = Users::where('status', 80)->where('doctor',1)->where('klinik_id', $getAdmin->klinik_id)->pluck('fullname', 'id')->toArray();
        $listUsers = [];
        if($getUsers) {
            foreach($getUsers as $key => $value) {
                $listUsers[$key] = $value;
            }
        }

        $getDoctorCategory = DoctorCategory::pluck('name', 'id')->toArray();
        $listDoctorCategory = [];
        if($getDoctorCategory) {
            foreach($getDoctorCategory as $key => $value) {
                $listDoctorCategory[$key] = $value;
            }
        }

        $service_id = [];
        foreach(Service::where('status', 80)->pluck('name', 'id')->toArray() as $key => $val) {
            $service_id[$key] = $val;
        };

        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.forms';
        $this->listView['create2'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.forms2';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.forms_edit';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.forms';
        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.list';

        $this->listView['schedule'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.schedule';

        $this->data['listSet']['user_id'] = $listUsers;
        $this->data['listSet']['service_id'] = $service_id;
        $this->data['listSet']['doctor_category_id'] = $listDoctorCategory;
        $this->listView['dataTable'] = env('ADMIN_TEMPLATE').'.page.doctor_clinic.list_button';

    }

    public function dataTable()
    {
        $this->callPermission();

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();
        $dataTables = new DataTables();

        $builder = $this->model::query()->selectRaw('doctor.id as id, users.fullname as user_id, doctor_category.name as doctor_category_id')
            ->join('users','users.id', '=', 'doctor.user_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->where('users.doctor',1)
            ->where('users.klinik_id', $getAdmin->klinik_id);

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
            if (in_array($list['type'], ['select', 'select2'])) {
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName) {
                    $getList = isset($this->data['listSet'][$fieldName]) ? $this->data['listSet'][$fieldName] : [];
                    return isset($getList[$query->$fieldName]) ? $getList[$query->$fieldName] : $query->$fieldName;
                });
            } else if (in_array($list['type'], ['money'])) {
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return number_format($query->$fieldName, 0);
                });
            } else if (in_array($list['type'], ['image'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<img src="' . asset($list['path'] . $query->$fieldName) . '" class="img-responsive max-image-preview"/>';
                });
            } else if (in_array($list['type'], ['image_preview'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<img src="' . $query->$fieldName . '" class="img-responsive max-image-preview"/>';
                });
            } else if (in_array($list['type'], ['code'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<pre>' . json_encode(json_decode($query->$fieldName, true), JSON_PRETTY_PRINT) . '"</pre>';
                });
            } else if (in_array($list['type'], ['texteditor'])) {
                $listRaw[] = $fieldName;
            }
        }

        return $dataTables
            ->rawColumns($listRaw)
            ->make(true);
    }

    public function edit($id){
        $this->callPermission();


        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $getDoctorService = DoctorService::where('doctor_id',$id)->get();

        $data['thisLabel'] = __('general.doctor');
        $data['viewType'] = 'edit';
        $data['formsTitle'] = __('general.title_create', ['field' => __('general.doctor') . ' ' . $getData->name]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;
        $data['doctorService'] = $getDoctorService;
        return view($this->listView[$data['viewType']], $data);
    }

    public function store()
    {
        $this->callPermission();

        $viewType = 'create';

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

        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $getData = $this->crud->store($data);

        $serviceId = $this->request->get('service_id');
        $price = clear_money_format($this->request->get('price'));

        foreach($serviceId as $key => $list){

            DoctorService::create([
                'doctor_id' => $getData->id,
                'service_id' => $list,
                'price' => $price[$key]
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

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getListCollectData = collectPassingData($this->passingData, $viewType);
        unset($getListCollectData['service_id']);

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

        $getData = $this->crud->update($data, $id);

        $serviceId = $this->request->get('service_id');
        $price = clear_money_format($this->request->get('price'));

        //dd($serviceId);

        if($serviceId){
            foreach($serviceId as $key => $list){
                //dd($list);
                DoctorService::where('doctor_id', $id)->update([
                    'doctor_id' => $getData->id,
                    'service_id' => $list,
                    'price' => $price[$key]
                ]);
            }
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
    public function show($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $getScheduleData  = DoctorSchedule::selectRaw('doctor_schedule.id, doctor_schedule.date_available,
         doctor_schedule.time_start,doctor_schedule.time_end, doctor_schedule.book,
         B.fullname AS doctor_id, C.name AS service_id')
            ->where('doctor_schedule.doctor_id', '=', $id)
            ->leftJoin('users AS B', 'B.id', '=', 'doctor_schedule.doctor_id')
            ->leftJoin('service AS C', 'C.id', '=', 'doctor_schedule.service_id')->get();



        $data = $this->data;

        $getDoctorService = DoctorService::where('doctor_id',$id)->get();

        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;
        $data['doctorService'] = $getDoctorService;
        $data['getScheduleData'] = $getScheduleData;

        return view($this->listView[$data['viewType']], $data);

    }

    public function schedule($id)
    {
        $this->callPermission();

        $getDoctor = Doctor::selectRaw('doctor.id, fullname AS name')->join('users', 'users.id', 'doctor.user_id')->where('doctor.id', $id)->first();
        if (!$getDoctor) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getTargetDate = strtotime($this->request->get('date')) > 0 ? date('Y-m-d', strtotime($this->request->get('date'))) : date('Y-m-d');

        $getListDate = DoctorSchedule::select('date_available')
            ->where('doctor_id', $getDoctor->id)->where('date_available', '>=', date('Y-m-d'))
            ->groupBy('date_available')
            ->orderBy('date_available', 'ASC')
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

        $getData = DoctorSchedule::where('date_available', $getTargetDate)->where('doctor_id', $getDoctor->id)->orderBy('id','DESC')->get();

        $data = $this->data;
        $data['parentLabel'] = $data['thisLabel'];
        $data['thisLabel'] = __('general.doctor_schedule');
        $data['listSet']['service'] = $this->data['listSet']['service_id'];
        $data['getDoctor'] = $getDoctor;
        $data['getListDate'] = $getListDate;
        $data['getTargetDate'] = $getTargetDate;
        $data['getData'] = $getData;

        return view($this->listView['schedule'], $data);
    }

    public function storeSchedule($id)
    {
        $this->callPermission();

        $data = $this->validate($this->request, [
            'service' => 'required',
            'date' => 'required',
            'time_start' => 'required',
            'time_end' => 'required'
        ]);

        $getServiceId = intval($data['service']);
        $getDate = strtotime($data['date']) > 0 ? date('Y-m-d', strtotime($data['date'])) : date('Y-m-d', strtotime("+1 day"));
        $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
        $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

        DoctorSchedule::create([
            'doctor_id' => $id,
            'service_id' => $getServiceId,
            'date_available' => $getDate,
            'time_start' => $getTimeStart,
            'time_end' => $getTimeEnd,
            'book' => 80
        ]);

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.schedule', $id);
        }
    }

    public function updateSchedule($id, $scheduleId)
    {
        $this->callPermission();

        $data = $this->validate($this->request, [
            'service' => 'required',
            'date' => 'required',
            'time_start' => 'required',
            'time_end' => 'required'
        ]);

        $getServiceId = intval($data['service']);
        $getDate = strtotime($data['date']) > 0 ? date('Y-m-d', strtotime($data['date'])) : date('Y-m-d', strtotime("+1 day"));
        $getTimeStart = strtotime($data['time_start']) > 0 ? date('H:i:00', strtotime($data['time_start'])) : date('H:i:00');
        $getTimeEnd = strtotime($data['time_end']) > 0 ? date('H:i:00', strtotime($data['time_end'])) : date('H:i:00');

        $getData = DoctorSchedule::where('doctor_id', $id)->where('id', $scheduleId)->first();
        if (!$getData) {
            if($this->request->ajax()){
                return response()->json(['result' => 2, 'message' => __('general.error_not_found')]);
            }
            else {
                session()->flash('message', __('general.error_not_found'));
                session()->flash('message_alert', 1);
                return redirect()->route($this->rootRoute.'.' . $this->route . '.schedule', $id);
            }
        }

        $getData->doctor_id = $id;
        $getData->service_id = $getServiceId;
        $getData->date_available = $getDate;
        $getData->time_start = $getTimeStart;
        $getData->time_end = $getTimeEnd;

        $getData->save();

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_edit_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_edit_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.schedule', $id);
        }
    }

    public function destroySchedule($id, $scheduleId)
    {
        $this->callPermission();

        $getData = DoctorSchedule::where('doctor_id', $id)->where('id', $scheduleId)->first();
        if (!$getData) {
            if($this->request->ajax()){
                return response()->json(['result' => 2, 'message' => __('general.error_not_found')]);
            }
            else {
                session()->flash('message', __('general.error_not_found'));
                session()->flash('message_alert', 1);
                return redirect()->route($this->rootRoute.'.' . $this->route . '.schedule', $id);
            }
        }

        $getData->delete();

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_delete_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_delete_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.schedule', $id);
        }
    }



    public function create2(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getData = Users::where('id', $adminId)->first();
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        if($this->request->get('download_example_import')) {
            $getLogic = new SynapsaLogic();
            $getLogic->downloadExampleImportDoctor();
        }

        $data = $this->data;

        $data['thisLabel'] = __('general.doctor');
        $data['viewType'] = 'create';
        $data['formsTitle'] = __('general.title_create', ['field' => __('general.doctor')]);
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
            'import_doctor' => 'required',
        ]);

        //A-N
        //A = Nomor
        //B = Kategori Produk
        //C = SKU
        //D = Nama Produk
        //E = Harga Produk
        //F = Unit Produk
        //G = Stock Produk
        //H = Stock Flag (1 Unlimited, 2 Limited)
        //I = Title(1)
        //J = Desc(1)
        //K = Title(2)
        //L = Desc(2)
        //M = Title(3)
        //N = Desc(3)
        //Start From Row 6

        $getFile = $this->request->file('import_doctor');

        if($getFile) {
            $destinationPath = 'synapsaapps/doctor/example_import';

            $getUrl = Storage::put($destinationPath, $getFile);

            die(env('OSS_URL') . '/' . $getUrl);

//            try {
//                $getFileName = $getFile->getClientOriginalName();
//                $ext = explode('.', $getFileName);
//                $ext = end($ext);
//                if (in_array(strtolower($ext), ['xlsx', 'xls'])) {
//                    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($getFile);
//                    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
//                    $data = $reader->load($getFile);
//
//                    if ($data) {
//                        $spreadsheet = $data->getActiveSheet();
//                        foreach ($spreadsheet->getRowIterator() as $key => $row) {
//                            if($key >= 6) {
//                                $kategoriProduk = $spreadsheet->getCell("B" . $key)->getValue();
//                                $sku = $spreadsheet->getCell("C" . $key)->getValue();
//                                $namaProduk = $spreadsheet->getCell("D" . $key)->getValue();
//                                $hargaProduk = $spreadsheet->getCell("E" . $key)->getValue();
//                                $unitProduk = $spreadsheet->getCell("F" . $key)->getValue();
//                                $stockProduk = $spreadsheet->getCell("G" . $key)->getValue();
//                                $stockFlag = strtolower(str_replace(' ', '', $spreadsheet->getCell("H" . $key)->getValue()));
//                                $title1 = $spreadsheet->getCell("I" . $key)->getValue();
//                                $desc1 = $spreadsheet->getCell("J" . $key)->getValue();
//                                $title2 = $spreadsheet->getCell("K" . $key)->getValue();
//                                $desc2 = $spreadsheet->getCell("L" . $key)->getValue();
//                                $title3 = $spreadsheet->getCell("M" . $key)->getValue();
//                                $desc3 = $spreadsheet->getCell("N" . $key)->getValue();
//
//                                $kategoriCheck = ProductCategory::where('name', $kategoriProduk)->first();
//                                if($kategoriCheck) {
//                                    $kategoriProduk = $kategoriCheck->id;
//                                }
//                                else {
//                                    if(strlen($kategoriProduk) > 0) {
//                                        $saveCategory = [
//                                            'name' => $kategoriProduk,
//                                            'status' => 80
//                                        ];
//
//                                        $productCategory = ProductCategory::create($saveCategory);
//                                        $kategoriProduk = $productCategory->id;
//                                    }
//                                }
//
//                                $flag = strtolower(str_replace(' ', '', $stockFlag));
//                                if($flag == 'unlimited') {
//                                    $stockFlag = 1;
//                                }
//                                else if($flag = 'limited') {
//                                    $stockFlag = 2;
//                                }
//                                else {
//                                    $stockFlag = 0;
//                                }
//
//                                $descProduct = [];
//                                if(strlen($title1) > 0) {
//                                    if(strlen($title2) > 0 && strlen($title3) > 0) {
//                                        $descProduct[] = [
//                                            'title' => [$title1, $title2, $title3],
//                                            'desc' => [$desc1, $desc2, $desc3],
//                                        ];
//                                    }
//                                    else if(strlen($title2) > 0 && strlen($title3) <= 0) {
//                                        $descProduct[] = [
//                                            'title' => [$title1, $title2],
//                                            'desc' => [$desc1, $desc2],
//                                        ];
//                                    }
//                                    else if(strlen($title3) > 0 && strlen($title2) <= 0) {
//                                        $descProduct[] = [
//                                            'title' => [$title1, $title3],
//                                            'desc' => [$desc1, $desc3],
//                                        ];
//                                    }
//                                    else {
//                                        $descProduct[] = [
//                                            'title' => [$title1],
//                                            'desc' => [$desc1],
//                                        ];
//                                    }
//                                }
//
//                                $saveData = [
//                                    'product_category_id' => $kategoriProduk,
//                                    'klinik_id' => $getAdmin->klinik_id,
//                                    'sku' => $sku,
//                                    'name' => $namaProduk,
//                                    'price' => $hargaProduk,
//                                    'unit' => $unitProduk,
//                                    'stock' => $stockProduk,
//                                    'stock_flag' => $stockFlag,
//                                    'desc' => json_encode($descProduct),
//                                    'status' => 80,
//                                ];
//
//                                if(strlen($namaProduk) > 0) {
//                                    Product::create($saveData);
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//            catch(\Exception $e) {
//                session()->flash('message', __('general.failed_import_doctor'));
//                session()->flash('message_alert', 1);
//                return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
//            }
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
