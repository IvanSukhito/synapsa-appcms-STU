<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\LabLogic;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Logic\UserLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\LabCart;
use App\Codes\Models\V1\LabSchedule;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Transaction;
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

    public function getLab()
    {
        $user = $this->request->attributes->get('_user');
        $serviceId = intval($this->request->get('service_id'));
        $priority = intval($this->request->get('priority'));
        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $getInterestService = $serviceId;

        $labLogic = new LabLogic();
        $getService = $labLogic->getListService($getInterestService);

        $getServiceId = $getService['getServiceId'] ?? 0;
        $getData = $labLogic->labGet($getLimit, $getServiceId, null, $s, $priority);

        return response()->json([
            'success' => 1,
            'data' => [
                'lab' => $getData['lab'],
                'service' => $getService['data'],
                'sub_service' => $getService['sub_service'],
                'active' => [
                    'service' => $getService['getServiceId'],
                    'service_name' => $getService['getServiceName'],
                    'sub_service' => $getService['getSubServiceId'],
                    'sub_service_name' => $getService['getSubServiceName'],
                ]
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getLabDetail($labId)
    {
        $user = $this->request->attributes->get('_user');
        $serviceId = intval($this->request->get('service_id'));

        $getInterestService = $serviceId > 0 ? $serviceId : $user->interest_service_id;

        $labLogic = new LabLogic();
        $getService = $labLogic->getListService($getInterestService);
        $getServiceId = $getService['getServiceId'] ?? 0;

        $getData = $labLogic->labInfo($labId, $getServiceId);

        return response()->json([
            'success' => 1,
            'data' => [
                'lab' => $getData['lab'],
                'lab_terkait' => $getData['child_lab']
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getCart(){

        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();
        $getData = $userLogic->userCartLab($user->id);

        return response()->json([
            'success' => 1,
            'data' => $getData,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

//        $getLabCart = LabCart::where('user_id', $user->id)->first();
//        $userId = $user->id;
//        $getServiceData = [];
//        $getData = [];
//        $total = 0;
//        if ($getLabCart) {
//            $getInterestService = $getLabCart->service_id;
//            $getServiceData = $this->getService($getInterestService);
//            $getData = $this->getLabInfo($userId, $getInterestService);
//            foreach ($getData as $list) {
//                $total += $list->price;
//            }
//        }
//
//        return response()->json([
//            'success' => 1,
//            'data' => [
//                'cart' => $getData,
//                'service' => $getServiceData,
//                'total' => $total,
//                'total_nice' => number_format($total, 0, ',', '.')
//            ],
//            'token' => $this->request->attributes->get('_refresh_token'),
//        ]);

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

        $userLogic = new UserLogic();
        $getResult = $userLogic->userCartLabAdd($userId, $getLabId, $getServiceId);
        if ($getResult != 80) {
            if ($getResult == 91) {
                $message = 'Test Lab menggunakan service yang berbeda';
            }
            else if ($getResult == 93) {
                $message = 'Test Lab tidak bisa di pesam bila tidak ada Tes Lab Utama';
            }
            else {
                $message = 'Test Lab tidak ditemukan';
            }
            return response()->json([
                'success' => 0,
                'message' => [$message],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getData = $userLogic->userCartLab($user->id);

        return response()->json([
            'success' => 1,
            'data' => $getData,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function deleteCart($labCartId)
    {
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();
        $getResult = $userLogic->userCartLabRemove($user->id, $labCartId);
        if ($getResult == 0) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

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

        $userLogic = new UserLogic();
        $labLogic = new LabLogic();

        $getCart = $userLogic->userCartLab($user->id, 1);
        $serviceId = $getCart['service_id'];
        $getLabSchedule = $labLogic->scheduleLabList($user->klinik_id, $serviceId, $getDate);
        $getService = Service::where('id', '=', $serviceId)->first();
        $getList = get_list_type_service();

        return response()->json([
            'success' => 1,
            'data' => [
                'schedule_start' => date('Y-m-d', strtotime("+1 day")),
                'schedule_end' => date('Y-m-d', strtotime("+366 day")),
                'address' => $getService->type == 2 ? 1 : 0,
                'address_nice' => $getList[$getService->type] ?? '-',
                'date' => $getDate,
                'schedule' => $getLabSchedule,
                'service_id' => $serviceId,
                'service' => $getCart['service'],
                'sub_service' => $getCart['sub_service']
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function scheduleAddress()
    {
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();

        return response()->json([
            'success' => 1,
            'data' => $userLogic->userAddress($user->id, $user->phone)
        ]);
    }

    public function scheduleSummary($scheduleId)
    {
        $user = $this->request->attributes->get('_user');
        $reqDate = strtotime($this->request->get('date'));

        $getData = $this->getScheduleDetail($user, $scheduleId, $reqDate);
        if ($getData['success'] != 1) {
            return response()->json([
                'success' => 0,
                'message' => $getData['message'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => $getData['data'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function getPayment($scheduleId)
    {
        $user = $this->request->attributes->get('_user');
        $reqDate = strtotime($this->request->get('date'));

        $getData = $this->getScheduleDetail($user, $scheduleId, $reqDate);
        if ($getData['success'] != 1) {
            return response()->json([
                'success' => 0,
                'message' => $getData['message'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getResult = $getData['data'];

        $getPayment = Payment::where('status', 80)->get();

        $getResult['payment'] = $getPayment;

        return response()->json([
            'success' => 1,
            'data' => $getResult,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function checkout()
    {
        $user = $this->request->attributes->get('_user');
        $scheduleId = intval($this->request->get('schedule_id'));
        $paymentId = intval($this->request->get('payment_id'));
        $getPhone = $this->request->get('phone');
        $subServiceId = intval($this->request->get('sub_service_id'));
        $reqDate = strtotime($this->request->get('date'));

        $synapsaLogic = new SynapsaLogic();
        $getPaymentResult = $synapsaLogic->checkPayment($paymentId);
        if ($getPaymentResult['success'] == 0) {
            return response()->json([
                'success' => 0,
                'message' => ['Payment Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $needPhone = intval($getPaymentResult['phone']);
        if ($needPhone == 1) {
            $validationRule = ['payment_id' => 'required|numeric', 'schedule_id' => 'required', 'date' => 'required', 'phone' => 'required|regex:/^(8\d+)/|numeric'];
        }
        else {
            $validationRule = ['payment_id' => 'required|numeric', 'schedule_id' => 'required', 'date' => 'required'];
        }

        $validator = Validator::make($this->request->all(), $validationRule);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getData = $this->getScheduleDetail($user, $scheduleId, $reqDate);
        if ($getData['success'] != 1) {
            return response()->json([
                'success' => 0,
                'message' => $getData['message'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $serviceId = $getData['data']['service_id'];
        $getDate = $getData['data']['date'];
        $getTime = $getData['data']['time'];
        $total = $getData['data']['total'];

        $getTotal = Transaction::where('klinik_id', '=', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT).rand(100000,999999);
        $sendData = [
            'job' => [
                'code' => $newCode,
                'payment_id' => $paymentId,
                'user_id' => $user->id,
                'type_service' => 'lab',
                'service_id' => $serviceId,
                'sub_service_id' => $subServiceId,
                'schedule_id' => $scheduleId,
                'date' => $getDate,
                'time' => $getTime
            ],
            'code' => $newCode,
            'total' => $total,
            'name' => $user->fullname
        ];

        if ($needPhone == 1) {
            $sendData['phone'] = $getPhone;
        }

        $getPayment = $getPaymentResult['payment'];

        $getPaymentInfo = $synapsaLogic->createPayment($getPayment, $sendData);
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
//
//
//
//
//
//        $userId = $user->id;
//
//        $needPhone = 0;
//        $validator = Validator::make($this->request->all(), [
//            'payment_id' => 'required|numeric',
//            'schedule_id' => 'required|numeric'
//        ]);
//        if ($validator->fails()) {
//            return response()->json([
//                'success' => 0,
//                'message' => $validator->messages()->all(),
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 422);
//        }
//
//        $paymentId = intval($this->request->get('payment_id'));
//        $scheduleId = intval($this->request->get('schedule_id'));
//        $getPayment = Payment::where('id', $paymentId)->first();
//        if (!$getPayment) {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Payment Tidak Ditemukan'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 422);
//        }
//
//        if ($getPayment->type == 2 && $getPayment->service == 'xendit' && in_array($getPayment->type_payment, ['ew_ovo', 'ew_dana', 'ew_linkaja'])) {
//            $needPhone = 1;
//            $validator = Validator::make($this->request->all(), [
//                'phone' => 'required|regex:/^(8\d+)/|numeric'
//            ]);
//            if ($validator->fails()) {
//                return response()->json([
//                    'success' => 0,
//                    'message' => $validator->messages()->all(),
//                    'token' => $this->request->attributes->get('_refresh_token'),
//                ], 422);
//            }
//        }
//
//        $getCart = LabCart::where('user_id', '=', $user->id)->where('choose', '=', 1)->first();
//        if (!$getCart) {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Test Lab Tidak Ditemukan'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 404);
//        }
//
//        $getLabSchedule = LabSchedule::where('service_id', $getCart->service_id)->where('id', '=', $scheduleId)
//            ->where('book', '=', 80)->where('klinik_id', $user->klinik_id)->first();
//        if (!$getLabSchedule) {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Jadwal Tidak Ditemukan'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 404);
//        }
//        else if ($getLabSchedule->book != 80) {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Jadwal Sudah Dipesan'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 404);
//        }
//        else if (date('Y-m-d', strtotime($getLabSchedule->date_available)) < date('Y-m-d')) {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Jadwal Sudah Lewat Waktunya'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 404);
//        }
//
//        $serviceId = $getCart->service_id;
//        $getData = $this->getLabInfo($userId, $serviceId);
//
//        $getData = $getData->where('choose',1);
//
//        if (!$getData) {
//            return response()->json([
//                'success' => 0,
//                'message' => ['Lab Tidak Ditemukan'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 404);
//        }
//
//        $total = 0;
//        foreach ($getData as $list) {
//            $total += $list->price;
//        }
//
//        $getTotal = Transaction::where('klinik_id', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
//            ->whereMonth('created_at', '=', date('m'))->count();
//
//        $newCode = str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT).rand(100,199);
//
//        $sendData = [
//            'job' => [
//                'code' => $newCode,
//                'payment_id' => $paymentId,
//                'user_id' => $user->id,
//                'type_service' => 'lab',
//                'lab_id' => $getLabSchedule->lab_id,
//                'service_id' => $getLabSchedule->service_id,
//                'schedule_id' => $getLabSchedule->id,
//                'lab_info' => $getData->toArray()
//            ],
//            'code' => $newCode,
//            'total' => $total,
//            'name' => $user->fullname
//        ];
//
//        if ($needPhone == 1) {
//            $sendData['phone'] = $this->request->get('phone');
//        }
//
//        $setLogic = new SynapsaLogic();
//        $getPaymentInfo = $setLogic->createPayment($getPayment, $sendData);
//        if ($getPaymentInfo['success'] == 1) {
//
//            return response()->json([
//                'success' => 1,
//                'data' => [
//                    'payment' => 0,
//                    'info' => $getPaymentInfo['info']
//                ],
//                'message' => ['Berhasil'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ]);
//        }
//        else {
//            return response()->json([
//                'success' => 0,
//                'message' => [$getPaymentInfo['message'] ?? '-'],
//                'token' => $this->request->attributes->get('_refresh_token'),
//            ], 422);
//        }

    }

    private function getScheduleDetail($user, $scheduleId, $reqDate)
    {
        $userLogic = new UserLogic();
        $labLogic = new LabLogic();
        $getLabCart = $userLogic->userCartLab($user->id, 1);
        if (count($getLabCart['cart']) <= 0) {
            return [
                'success' => 0,
                'message' => ['Test Lab Tidak Ditemukan'],
            ];
        }
        $total = $getLabCart['total'];
        $totalNice = $getLabCart['total_nice'];
        $serviceId = $getLabCart['service_id'];

        $getLabSchedule = $labLogic->scheduleCheck($scheduleId, $reqDate, 1);
        if ($getLabSchedule['success'] != 80) {
            return [
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
            ];
        }

        return [
            'success' => 1,
            'data' => [
                'cart' => $getLabCart['cart'],
                'schedule' => $getLabSchedule['schedule'],
                'service_id' => $serviceId,
                'date' => $getLabSchedule['date'],
                'time' => $getLabSchedule['time'],
                'total' => $total,
                'total_nice' => $totalNice
            ]
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
