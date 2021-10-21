<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\LabCart;
use App\Codes\Models\V1\LabSchedule;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\UsersAddress;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LabController extends Controller
{
    protected $request;
    protected $setting;
    protected $limit;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->limit = 10;
        $this->setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
    }

    public function getLab(){

        $user = $this->request->attributes->get('_user');

        $serviceId = intval($this->request->get('service_id'));

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $getInterestService = $serviceId > 0 ? $serviceId : $user->interest_service_id;

        $getServiceData = $this->getService($getInterestService);

        $data = Lab::selectRaw('lab.id ,lab.name, lab_service.price, lab.image')
        ->join('lab_service', 'lab_service.lab_id','=','lab.id')
        ->where('lab_service.service_id','=', $getServiceData['getServiceId'])
        ->where('lab.parent_id', '=', 0);

        if (strlen($s) > 0) {
            $data = $data->where('name', 'LIKE', "%$s%");
        }

        if ($this->request->get('priority')) {
            $data = $data->where('priority', 1);
        }

        $data = $data->orderBy('name', 'ASC')->paginate($getLimit);

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'lab' => $data,
                'service' => $getServiceData['data'],
                'active' => [
                    'service' => $getServiceData['getServiceId'],
                    'service_name' => $getServiceData['getServiceName'],
                ]
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getLabDetail($id){

        $user = $this->request->attributes->get('_user');

        $serviceId = intval($this->request->get('service_id'));

        $getInterestService = $serviceId > 0 ? $serviceId : $user->interest_service_id;

        $getServiceData = $this->getService($getInterestService);

        $data = Lab::selectRaw('lab.parent_id, lab.name, lab.image, lab.desc_lab,lab.desc_benefit,
            lab.desc_preparation, lab.recommended_for, lab_service.price')
            ->join('lab_service', 'lab_service.lab_id','=','lab.id')
            ->where('lab_service.service_id','=', $getServiceData['getServiceId'])
            ->where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        if ($data->parent_id == 0) {
            $dataLabTerkait = Lab::selectRaw('lab.id ,lab.name, lab_service.price, lab.image')
                ->join('lab_service', 'lab_service.lab_id','=','lab.id', 'LEFT')
                ->where('lab_service.service_id','=', $getServiceData['getServiceId'])
                ->where('lab.parent_id', '=', $id)->get();

            $getData = [
                'lab' => $data,
                'lab_terkait' => $dataLabTerkait
            ];
        }
        else {
            $getData = [
                'lab' => $data
            ];
        }

        return response()->json([
            'success' => 1,
            'data' => $getData,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getCart(){

        $user = $this->request->attributes->get('_user');

        $getLabCart = LabCart::where('user_id', $user->id)->first();

        $userId = $user->id;

        $getServiceData = [];
        $getData = [];
        $total = 0;
        if ($getLabCart) {

            $getInterestService = $getLabCart->service_id;

            $getServiceData = $this->getService($getInterestService);

            $getData = $this->getLabInfo($userId, $getInterestService);

            foreach ($getData as $list) {
                $total += $list->price;
            }
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'cart' => $getData,
                'service' => $getServiceData,
                'total' => $total,
                'total_nice' => number_format($total, 0, ',', '.')
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function storeCart()
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'lab_id' => 'required|numeric',
            'service_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getLabId = $this->request->get('lab_id');
        $getServiceId = $this->request->get('service_id');
        $userId = $user->id;

        $getLabCart = LabCart::where('user_id', $user->id)->first();
        if ($getLabCart && $getLabCart->service_id != $getServiceId) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab menggunakan service yang berbeda'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        LabCart::firstOrCreate([
            'user_id' => $user->id,
            'lab_id' => $getLabId,
            'service_id' => $getServiceId,
        ]);

        $total = 0;
        $getService = Service::where('id', $getServiceId)->where('status', '=', 80)->first();

        $getData = $this->getLabInfo($userId, $getServiceId);

        foreach ($getData as $list) {
            $total += $list->price;
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'cart' => $getData,
                'service' => $getService,
                'total' => $total,
                'total_nice' => number_format($total, 0, ',', '.')
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function deleteCart($id)
    {
        $user = $this->request->attributes->get('_user');

        $getData = LabCart::where('user_id', $user->id)->where('id', $id)->first();
        if (!$getData) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getData->delete();

        return response()->json([
            'success' => 1,
            'message' => ['Test Lab berhasil dihapus'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function chooseCart()
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'cart_ids' => 'required|array'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getCartIds = $this->request->get('cart_ids');

        DB::beginTransaction();
        $getData = LabCart::where('user_id', $user->id)->get();
        $haveProduct = 0;
        foreach ($getData as $list) {
            if (in_array($list->id, $getCartIds)) {
                $haveProduct = 1;
                $list->choose = 1;
            }
            else {
                $list->choose = 0;
            }

            $list->save();
        }
        DB::commit();

        if ($haveProduct > 0) {
            return response()->json([
                'success' => 1,
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        else {
            return response()->json([
                'success' => 0,
                'message' => ['Tidak ada Test Lab yang di pilih'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

    }

    public function listBookLab()
    {
        $user = $this->request->attributes->get('_user');
        $getDate = strtotime($this->request->get('date')) > 0 ?
            date('Y-m-d', strtotime($this->request->get('date'))) :
            date('Y-m-d', strtotime("+1 day"));

        $getData = LabCart::where('user_id', '=', $user->id)->where('choose', '=', 1)->first();
        if (!$getData) {
            return response()->json([
                'success' => 0,
                'message' => ['Tidak ada Test Lab yang di pilih'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getInterestService = $getData->service_id;
        $getService = Service::where('id', $getInterestService)->where('status', '=', 80)->first();
        $getServiceData = $this->getService($getInterestService);
        $getLabSchedule = LabSchedule::where('service_id', $getData->service_id)
            ->where('date_available', '=', $getDate)
            ->get();

        //dd($getServiceData);

        $getList = get_list_type_service();

        //dd($getList);

        return response()->json([
            'success' => 1,
            'data' => [
                'schedule_start' => date('Y-m-d', strtotime("+1 day")),
                'schedule_end' => date('Y-m-d', strtotime("+366 day")),
                'address' => $getService->type == 2 ? 1 : 0,
                'address_nice' => $getList[$getService->type] ?? '-',
                'date' => $getDate,
                'schedule' => $getLabSchedule,
                'service' => $getServiceData
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function scheduleAddress()
    {
        $user = $this->request->attributes->get('_user');

        $getUsersAddress = UsersAddress::where('user_id', $user->id)->first();

        $getAddressName = $getUsersAddress->address_name ?? $user->address ?? '' ;
        $getAddress = $getUsersAddress->address ?? $user->address_detail ?? '';
        $getProvince = $getUsersAddress->province_id ?? $user->province_id ?? '';
        $getCity = $getUsersAddress->city_id ?? $user->city_id ?? '';
        $getDistrict = $getUsersAddress->district_id ?? $user->district_id ?? '';
        $getSubDistrict = $getUsersAddress->sub_district_id ?? $user->sub_district_id ?? '';
        $getZipCode = $getUsersAddress->zip_code ?? $user->zip_code ?? '';
        $getPhone = $user->phone ?? '';

        return response()->json([
            'success' => 1,
            'data' => [
                'address_name' => $getAddressName,
                'address' => $getAddress,
                'province_id' => $getProvince,
                'city_id' => $getCity,
                'district_id' => $getDistrict,
                'sub_district_id' => $getSubDistrict,
                'zip_code' => $getZipCode,
                'phone' => $getPhone
            ]
        ]);
    }

    public function scheduleSummary($id)
    {
        $user = $this->request->attributes->get('_user');

        $userId = $user->id;
        $getCart = LabCart::where('user_id', '=', $user->id)->where('choose', '=', 1)->first();
        if (!$getCart) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getLabSchedule = LabSchedule::where('service_id', $getCart->service_id)->where('id', '=', $id)
            ->where('book', '=', 80)->first();
        if (!$getLabSchedule) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $total = 0;

        $serviceId = $getCart->service_id;

        $getData = $this->getLabInfo($userId, $serviceId);

        $getData = $getData->where('choose',1);

        $temp = [];
        foreach ($getData as $list) {
            $total += $list->price;
            $temp[] = $list;
        }
        $getData = $temp;

        return response()->json([
            'success' => 1,
            'data' => [
                'cart' => $getData,
                'schedule' => $getLabSchedule,
                'total' => $total,
                'total_nice' => number_format($total, 0, ',', '.')
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function getPayment($id)
    {
        $user = $this->request->attributes->get('_user');

        $userId = $user->id;

        $getCart = LabCart::where('user_id', '=', $user->id)->where('choose', '=', 1)->first();
        if (!$getCart) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getLabSchedule = LabSchedule::where('service_id', $getCart->service_id)->where('id', '=', $id)
            ->where('book', '=', 80)->first();
        if (!$getLabSchedule) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $total = 0;

        $serviceId = $getCart->service_id;

        $getData = $this->getLabInfo($userId, $serviceId);

        $getData = $getData->where('choose',1);

        foreach ($getData as $list) {
            $total += $list->price;
        }

        $getPayment = Payment::where('status', 80)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'cart' => $getData,
                'schedule' => $getLabSchedule,
                'total' => $total,
                'total_nice' => number_format($total, 0, ',', '.'),
                'payment' => $getPayment
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function checkout()
    {
        $user = $this->request->attributes->get('_user');

        $userId = $user->id;

        $needPhone = 0;
        $validator = Validator::make($this->request->all(), [
            'payment_id' => 'required|numeric',
            'schedule_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $paymentId = intval($this->request->get('payment_id'));
        $scheduleId = intval($this->request->get('schedule_id'));
        $getPayment = Payment::where('id', $paymentId)->first();
        if (!$getPayment) {
            return response()->json([
                'success' => 0,
                'message' => ['Payment Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        if ($getPayment->type == 2 && $getPayment->service == 'xendit' && in_array($getPayment->type_payment, ['ew_ovo', 'ew_dana', 'ew_linkaja'])) {
            $needPhone = 1;
            $validator = Validator::make($this->request->all(), [
                'phone' => 'required|regex:/^(8\d+)/|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $validator->messages()->all(),
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 422);
            }
        }

        $getCart = LabCart::where('user_id', '=', $user->id)->where('choose', '=', 1)->first();
        if (!$getCart) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getLabSchedule = LabSchedule::where('service_id', $getCart->service_id)->where('id', '=', $scheduleId)
            ->where('book', '=', 80)->first();
        if (!$getLabSchedule) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else if ($getLabSchedule->book != 80) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Sudah Dipesan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else if (date('Y-m-d', strtotime($getLabSchedule->date_available)) < date('Y-m-d')) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Sudah Lewat Waktunya'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $serviceId = $getCart->service_id;
        $getData = $this->getLabInfo($userId, $serviceId);

        $getData = $getData->where('choose',1);

        if (!$getData) {
            return response()->json([
                'success' => 0,
                'message' => ['Lab Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $total = 0;
        foreach ($getData as $list) {
            $total += $list->price;
        }

        $getTotal = Transaction::where('klinik_id', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT).rand(100,199);

        $sendData = [
            'job' => [
                'code' => $newCode,
                'payment_id' => $paymentId,
                'user_id' => $user->id,
                'type_service' => 'lab',
                'lab_id' => $getLabSchedule->lab_id,
                'service_id' => $getLabSchedule->service_id,
                'schedule_id' => $getLabSchedule->id,
                'lab_info' => $getData->toArray()
            ],
            'code' => $newCode,
            'total' => $total,
            'name' => $user->fullname
        ];

        if ($needPhone == 1) {
            $sendData['phone'] = $this->request->get('phone');
        }

        $setLogic = new SynapsaLogic();
        $getPaymentInfo = $setLogic->createPayment($getPayment, $sendData);
        if ($getPaymentInfo['success'] == 1) {

            return response()->json([
                'success' => 1,
                'data' => [
                    'payment' => 0,
                    'info' => $getPaymentInfo['info']
                ],
                'message' => ['Berhasil'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        else {
            return response()->json([
                'success' => 0,
                'message' => [$getPaymentInfo['message'] ?? '-'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

//        $job = SetJob::create([
//            'status' => 1,
//            'params' => json_encode([
//                'payment_id' => $paymentId,
//                'user_id' => $user->id,
//                'type_service' => 'lab',
//                'lab_id' => $getLabSchedule->lab_id,
//                'service_id' => $getLabSchedule->service_id,
//                'schedule_id' => $getLabSchedule->id,
//                'lab_info' => $getData->toArray()
//            ])
//        ]);
//
//        dispatch((new ProcessTransaction($job->id))->onQueue('high'));
////        ProcessTransaction::dispatch($job->id);
//
//        return response()->json([
//            'success' => 1,
//            'data' => [
//                'job_id' => $job->id
//            ],
//            'message' => ['Berhasil'],
//            'token' => $this->request->attributes->get('_refresh_token'),
//        ]);

    }

    private function getService($getInterestService) {

        $getServiceLab = isset($this->setting['service-lab']) ? json_decode($this->setting['service-lab'], true) : [];
        if (count($getServiceLab) > 0) {
            $service = Service::whereIn('id', $getServiceLab)->where('status', '=', 80)->orderBy('orders', 'ASC')->get();
        }
        else {
            $service = Service::where('status', '=', 80)->orderBy('orders', 'ASC')->get();
        }

        $tempService = [];
        $firstService = 0;
        $getServiceId = 0;
        $getServiceDataTemp = false;
        $getServiceData = false;
        foreach ($service as $index => $list) {
            $temp = [
                'id' => $list->id,
                'name' => $list->name,
                'type' => $list->type,
                'type_nice' => $list->type_nice,
                'active' => 0
            ];

            if ($index == 0) {
                $firstService = $list->id;
                $getServiceDataTemp = $list;
            }

            if ($list->id == $getInterestService) {
                $temp['active'] = 1;
                $getServiceId = $list->id;
                $getServiceData = $list;
            }

            $tempService[] = $temp;
        }

        $service = $tempService;
        if ($getServiceId == 0) {
            if ($firstService > 0) {
                $service[0]['active'] = 1;
            }
            $getServiceId = $firstService;
            $getServiceData = $getServiceDataTemp;
        }

        return [
            'data' => $service,
            'getServiceId' => $getServiceId,
            'getServiceName' => $getServiceData ? $getServiceData->name : ''
        ];

    }

    private function getLabInfo($userId, $serviceId){

        return Lab::selectRaw('lab_cart.id, lab.id AS lab_id, lab.klinik_id as klinik_id, lab.parent_id ,lab.name, lab_service.price,
                lab.image, lab_cart.choose')
        ->join('lab_service', 'lab_service.lab_id','=','lab.id')
        ->join('lab_cart', 'lab_cart.lab_id','=','lab.id')
        ->where('lab_service.service_id', $serviceId)
        ->where('user_id', $userId)->get();

    }
}
