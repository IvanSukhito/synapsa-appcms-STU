<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\DoctorLogic;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Logic\UserLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Payment;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DoctorController extends Controller
{
    protected $request;
    protected $setting;
    protected $limit;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
        $this->limit = 5;
    }

    public function getDoctor()
    {
        $user = $this->request->attributes->get('_user');

        $serviceId = intval($this->request->get('service_id'));
        $subServiceId = intval($this->request->get('sub_service_id'));
        $categoryId = intval($this->request->get('category_id'));
        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $getInterestService = $serviceId > 0 ? $serviceId : $user->interest_service_id;
        $getInterestCategory = $categoryId > 0 ? $categoryId : $user->interest_category_id;

        $doctorLogic = new DoctorLogic();
        $getService = $doctorLogic->getListService($getInterestService, $subServiceId);
        $getCategory = $doctorLogic->getListCategory($getInterestCategory);

        $getServiceId = $getService['getServiceId'] ?? 0;
        $getCategoryId = $getCategory['getCategoryId'] ?? 0;

        $getData = $doctorLogic->doctorList($getServiceId, $getCategoryId, $s, $getLimit);

        return response()->json([
            'success' => 1,
            'data' => [
                'doctor' => $getData,
                'service' => $getService['data'],
                'sub_service' => $getService['sub_service'],
                'active' => [
                    'service' => $getService['getServiceId'],
                    'service_name' => $getService['getServiceName'],
                    'sub_service' => $getService['getSubServiceId'],
                    'sub_service_name' => $getService['getSubServiceName'],
                    'category' => $getCategory['getCategoryId'],
                    'category_name' => $getCategory['getCategoryName']
                ]
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function doctorCategory()
    {
        $doctorLogic = new DoctorLogic();
        $getCategory = $doctorLogic->getListCategory();

        return response()->json([
            'success' => 1,
            'data' => [
                'category' => $getCategory,
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function getDoctorDetail($doctorId)
    {
        $serviceId = intval($this->request->get('service_id'));
        if ($serviceId <= 0) {
            return response()->json([
                'success' => 0,
                'message' => ['Service Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $doctorLogic = new DoctorLogic();
        $data = $doctorLogic->doctorInfo($doctorId, $serviceId);
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Doktor Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else {
            return response()->json([
                'success' => 1,
                'data' => $data,
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

    }

    public function listBookDoctor($doctorId)
    {
        $serviceId = $this->request->get('service_id');
        $getDate = strtotime($this->request->get('date')) > 0 ?
            date('Y-m-d', strtotime($this->request->get('date'))) :
            date('Y-m-d', strtotime("+1 day"));

        if ($getDate < date('Y-m-d')) {
            return response()->json([
                'success' => 0,
                'message' => ['Waktu sudah lewat'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getService = Service::where('id', '=', $serviceId)->where('status', '=', 80)->first();
        if (!$getService) {
            return response()->json([
                'success' => 0,
                'message' => ['Service Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $doctorLogic = new DoctorLogic();
        $data = $doctorLogic->doctorInfo($doctorId, $serviceId);
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Doktor Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getSchedule = $doctorLogic->scheduleDoctorList($doctorId, $serviceId, $getDate);
        $getList = get_list_type_service();

        return response()->json([
            'success' => 1,
            'data' => [
                'schedule_start' => date('Y-m-d', strtotime("now")),
                'schedule_end' => date('Y-m-d', strtotime("+1 year")),
                'address' => $getService->type == 2 ? 1 : 0,
                'address_nice' => $getList[$getService->type] ?? '-',
                'date' => $getDate,
                'schedule' => $getSchedule,
                'doctor' => $data,
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function checkSchedule($scheduleId)
    {
        $user = $this->request->attributes->get('_user');

        $reqDate = strtotime($this->request->get('date'));
        $getData = $this->getScheduleDetail($user, $scheduleId, $reqDate);
        if ($getData['success'] == 0) {
            return response()->json([
                'success' => 0,
                'message' => $getData,
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        return response()->json([
            'success' => 1,
            'data' => $getData['data'],
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
        $subServiceId = intval($this->request->get('sub_service_id'));

        $getData = $this->getScheduleDetail($user, $scheduleId, $reqDate);

        $serviceId = $getData['data']['service_id'];

        $doctorLogic = new DoctorLogic();
        $getService = $doctorLogic->getListService($serviceId, $subServiceId);

        if ($getData['success'] == 0) {
            return response()->json([
                'success' => 0,
                'message' => $getData,
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        return response()->json([
            'success' => 1,
            'data' => $getData['data'],
            'active' => [
                'service' => $getService['getServiceId'],
                'service_name' => $getService['getServiceName'],
                'sub_service' => $getService['getSubServiceId'],
                'sub_service_name' => $getService['getSubServiceName'],
            ],

            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getPayment($scheduleId)
    {
        $user = $this->request->attributes->get('_user');

        $reqDate = strtotime($this->request->get('date'));
        $getData = $this->getScheduleDetail($user, $scheduleId, $reqDate);
        if ($getData['success'] == 0) {
            return response()->json([
                'success' => 0,
                'message' => $getData,
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getResult = $getData['data'];


        $getPayment = Payment::where('status', '=', 80)->get();
        $getResult['payment'] = $getPayment;

        return response()->json([
            'success' => 1,
            'data' => $getResult,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function checkout($scheduleId)
    {
        $user = $this->request->attributes->get('_user');

        $synapsaLogic = new SynapsaLogic();

        $paymentId = intval($this->request->get('payment_id'));
        $subServiceId = intval($this->request->get('sub_service_id'));
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
            $validationRule = ['payment_id' => 'required|numeric', 'date' => 'required', 'phone' => 'required|regex:/^(8\d+)/|numeric'];
        }
        else {
            $validationRule = ['payment_id' => 'required|numeric', 'date' => 'required'];
        }

        $validator = Validator::make($this->request->all(), $validationRule);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $reqDate = strtotime($this->request->get('date'));
        $getPhone = $this->request->get('phone');
        $getPayment = $getPaymentResult['payment'];

        $getData = $this->getScheduleDetail($user, $scheduleId, $reqDate);
        if ($getData['success'] == 0) {
            return response()->json([
                'success' => 0,
                'message' => $getData,
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getSchedule = $getData['data']['schedule'];
        $getDate = $getData['data']['date'];
        $getTime = $getData['data']['time'];
        $getDoctor = $getData['data']['doctor'];
        $doctorId = $getSchedule->doctor_id;
        $serviceId = $getSchedule->service_id;
        $scheduleId = $getSchedule->id;
        $total = $getDoctor->price;

        $getTotal = Transaction::where('klinik_id', '=', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT).rand(100000,999999);
        $sendData = [
            'job' => [
                'code' => $newCode,
                'payment_id' => $paymentId,
                'user_id' => $user->id,
                'type_service' => 'doctor',
                'doctor_id' => $doctorId,
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

    }

    private function getScheduleDetail($user, $scheduleId, $getDateTime)
    {
        $getDate = date('Y-m-d', $getDateTime);

        if ($getDate < date('Y-m-d')) {
            return [
                'success' => 0,
                'message' => ['Jadwal Sudah Lewat Waktunya'],
            ];
        }

        $doctorLogic = new DoctorLogic();
        $getDoctorSchedule = $doctorLogic->scheduleCheck($scheduleId, $getDate, $user->id, 1);
        if ($getDoctorSchedule['success'] == 80) {
            $getSchedule = $getDoctorSchedule['schedule'];
            $doctorId = $getSchedule->doctor_id;
            $serviceId = $getSchedule->service_id;
            $getTime = $getSchedule->time_start;
            $getService = Service::where('id', '=', $serviceId)->first();
            $getList = get_list_type_service();

            return [
                'success' => 1,
                'data' => [
                    'schedule' => $getSchedule,
                    'address' => $getService->type == 2 ? 1 : 0,
                    'address_nice' => $getList[$getService->type] ?? '-',
                    'doctor' => $doctorLogic->doctorInfo($doctorId, $serviceId),
                    'date' => $getDate,
                    'time' => $getTime,
                    'service_id' => $serviceId
                ],

            ];
        }
        else if ($getDoctorSchedule['success'] == 90) {
            return [
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
            ];
        }
        else if ($getDoctorSchedule['success'] == 91) {
            return [
                'success' => 0,
                'message' => ['Jadwal Tidak tersedia'],
            ];
        }
        else if ($getDoctorSchedule['success'] == 92) {
            return [
                'success' => 0,
                'message' => ['Jadwal Sudah Dipesan'],
            ];
        }
        else if ($getDoctorSchedule['success'] == 93) {
            return [
                'success' => 0,
                'message' => ['Waktu date tidak sama'],
            ];
        }
        else {
            return [
                'success' => 0,
                'message' => ['Doctor Error'],
            ];
        }

    }

}
