<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\DoctorLogic;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
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
        $categoryId = intval($this->request->get('category_id'));
        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $getInterestService = $serviceId > 0 ? $serviceId : $user->interest_service_id;
        $getInterestCategory = $categoryId > 0 ? $categoryId : $user->interest_category_id;

        $doctorLogic = new DoctorLogic();
        $getService = $doctorLogic->getService($getInterestService);
        $getCategory = $doctorLogic->getCategory($getInterestCategory);

        $getServiceId = $getService['getServiceId'] ?? 0;
        $getCategoryId = $getCategory['getCategoryId'] ?? 0;

        $getData = $doctorLogic->doctorList($getServiceId, $getCategoryId, $s, $getLimit);

        return response()->json([
            'success' => 1,
            'data' => [
                'doctor' => $getData,
                'service' => $getService['data'],
                'active' => [
                    'service' => $getService['getServiceId'],
                    'service_name' => $getService['getServiceName'],
                    'category' => $getCategory['getCategoryId'],
                    'category_name' => $getCategory['getCategoryName']
                ]
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function doctorCategory()
    {
        $getDoctorCategory = Cache::remember('doctor_category', env('SESSION_LIFETIME'), function () {
            return DoctorCategory::orderBy('orders', 'ASC')->get();
        });

        return response()->json([
            'success' => 1,
            'data' => [
                'category' => $getDoctorCategory,
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function getDoctorDetail($id)
    {
        $serviceId = $this->request->get('service_id');
        $getService = Service::where('id', $serviceId)->where('status', '=', 80)->first();
        if (!$getService) {
            return response()->json([
                'success' => 0,
                'message' => ['Service Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data = $this->getDoctorInfo($id, $serviceId);

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

    public function listBookDoctor($id)
    {
        $serviceId = $this->request->get('service_id');
        $getDate = strtotime($this->request->get('date')) > 0 ?
            date('Y-m-d', strtotime($this->request->get('date'))) :
            date('Y-m-d', strtotime("+1 day"));

        $getService = Service::where('id', $serviceId)->where('status', '=', 80)->first();
        if (!$getService) {
            return response()->json([
                'success' => 0,
                'message' => ['Service Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data = $this->getDoctorInfo($id, $serviceId);
//        $data = $this->getDoctorInfo(13, $serviceId);

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Doktor Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else {

            $getDoctorSchedule2 = DoctorSchedule::where('doctor_id', '=', $id)->where('service_id', '=', $serviceId)
                ->where('date_available', '=', $getDate)->get();
            if ($getDoctorSchedule2->count() > 0) {
                $getSchedule = $getDoctorSchedule2;
            }
            else {
                $getWeekday = intval(date('w', strtotime($getDate)));
                $getDoctorSchedule = DoctorSchedule::where('doctor_id', '=', $id)->where('service_id', $serviceId)
                    ->where('weekday', $getWeekday)->get();
                $getSchedule = $getDoctorSchedule;
            }

            $temp = [];
            foreach ($getSchedule as $list) {
                $temp[] = [
                    'id' => $list->id,
                    'doctor_id' => $id,
                    'service_id' => $serviceId,
                    'date_available' => $getDate,
                    'time_start' => $list->time_start,
                    'time_end' => $list->time_end,
                    'type' => $list->type,
                    'weekday' => $list->weekday,
                    'book' => $list->book,
                    'book_nice' => $list->book_nice
                ];
            }
            $getDoctorSchedule = $temp;

            $getList = get_list_type_service();

            return response()->json([
                'success' => 1,
                'data' => [
                    'schedule_start' => date('Y-m-d', strtotime("now")),
                    'schedule_end' => date('Y-m-d', strtotime("+1 year")),
                    'address' => $getService->type == 2 ? 1 : 0,
                    'address_nice' => $getList[$getService->type] ?? '-',
                    'date' => $getDate,
                    'schedule' => $getDoctorSchedule,
                    'doctor' => $data,
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);

        }
    }

    public function checkSchedule($id)
    {
        $getDoctorSchedule = DoctorSchedule::where('id', '=', $id)->first();

        if (!$getDoctorSchedule) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else if ($getDoctorSchedule->book != 80) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Sudah Dipesan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }
        else if (date('Y-m-d', strtotime($getDoctorSchedule->date_available)) < date('Y-m-d')) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Sudah Lewat Waktunya'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getService = Service::where('id', $getDoctorSchedule->service_id)->where('status', '=', 80)->first();
        $getList = get_list_type_service();

        return response()->json([
            'success' => 1,
            'data' => [
                'schedule' => $getDoctorSchedule,
                'address' => $getService->type == 2 ? 1 : 0,
                'address_nice' => $getList[$getService->type] ?? '-',
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function scheduleAddress()
    {
        $user = $this->request->attributes->get('_user');

        return response()->json([
            'success' => 1,
            'data' => $this->getUserAddress($user->id)
        ]);
    }

    public function scheduleSummary($id)
    {
        $user = $this->request->attributes->get('_user');

        $getDoctorSchedule = DoctorSchedule::where('id', '=', $id)->first();
        if (!$getDoctorSchedule) {

            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);

        }
        else if ($getDoctorSchedule->book != 80) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Sudah Dipesan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }
        else if (date('Y-m-d', strtotime($getDoctorSchedule->date_available)) < date('Y-m-d')) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Sudah Lewat Waktunya'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getService = Service::where('id', $getDoctorSchedule->service_id)->where('status', '=', 80)->first();

        $doctorId = $getDoctorSchedule->doctor_id;
        $serviceId = $getDoctorSchedule->service_id;

        $data = $this->getDoctorInfo($doctorId, $serviceId);

        $result = [
            'schedule' => $getDoctorSchedule,
            'doctor' => $data,
            'service' => $getService
        ];

        if ($getService->type == 2) {
            $result['address'] = $this->getUserAddress($user->id);
        }

        return response()->json([
            'success' => 1,
            'data' => $result,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getPayment($id)
    {
        $getDoctorSchedule = DoctorSchedule::where('id', '=', $id)->first();
        if (!$getDoctorSchedule) {

            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);

        }
        else if ($getDoctorSchedule->book != 80) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Sudah Dipesan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }
        else if (date('Y-m-d', strtotime($getDoctorSchedule->date_available)) < date('Y-m-d')) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Sudah Lewat Waktunya'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $doctorId = $getDoctorSchedule->doctor_id;
        $serviceId = $getDoctorSchedule->service_id;

        $data = $this->getDoctorInfo($doctorId, $serviceId);

        $getPayment = Payment::where('status', 80)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'schedule' => $getDoctorSchedule,
                'doctor' => $data,
                'payment' => $getPayment
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function checkout($id)
    {
        $user = $this->request->attributes->get('_user');

        $needPhone = 0;
        $validator = Validator::make($this->request->all(), [
            'payment_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $paymentId = intval($this->request->get('payment_id'));
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

        $getDoctorSchedule = DoctorSchedule::where('id', '=', $id)->first();
        if (!$getDoctorSchedule) {

            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);

        }
        else if ($getDoctorSchedule->book != 80) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Sudah Dipesan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }
        else if (date('Y-m-d', strtotime($getDoctorSchedule->date_available)) < date('Y-m-d')) {
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Sudah Lewat Waktunya'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $paymentId = $this->request->get('payment_id');
        $doctorId = $getDoctorSchedule->doctor_id;
        $serviceId = $getDoctorSchedule->service_id;

        $data = $this->getDoctorInfo($doctorId, $serviceId);
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Doktor Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $total = $data->price;

        $getTotal = Transaction::where('klinik_id', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT).rand(100,199);

        $sendData = [
            'job' => [
                'code' => $newCode,
                'payment_id' => $paymentId,
                'user_id' => $user->id,
                'type_service' => 'doctor',
                'doctor_id' => $getDoctorSchedule->doctor_id,
                'service_id' => $getDoctorSchedule->service_id,
                'schedule_id' => $getDoctorSchedule->id,
                'doctor_info' => $data->toArray()
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
//                'type_service' => 'doctor',
//                'doctor_id' => $getDoctorSchedule->doctor_id,
//                'service_id' => $getDoctorSchedule->service_id,
//                'schedule_id' => $getDoctorSchedule->id,
//                'doctor_info' => $data->toArray()
//            ])
//        ]);
//
//        dispatch((new ProcessTransaction($job->id))->onQueue('high'));
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

    private function getService($getInterestService)
    {

        $getServiceDoctor = isset($this->setting['service-doctor']) ? json_decode($this->setting['service-doctor'], true) : [];
        if (count($getServiceDoctor) > 0) {
            $service = Service::whereIn('id', $getServiceDoctor)->where('status', '=', 80)->orderBy('orders', 'ASC')->get();
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

    private function getCategory($getInterestCategory)
    {
        $category = Cache::remember('doctor_category', env('SESSION_LIFETIME'), function () {
            return DoctorCategory::orderBy('orders', 'ASC')->get();
        });

        $tempCategory = [];
        $firstCategory = 0;
        $getCategoryId = 0;
        $getCategoryDataTemp = false;
        $getCategoryData = false;
        foreach ($category as $index => $list) {
            $temp = [
                'id' => $list->id,
                'name' => $list->name,
                'active' => 0
            ];

            if ($index == 0) {
                $firstCategory = $list->id;
                $getCategoryDataTemp = $list;
            }

            if ($list->id == $getInterestCategory) {
                $temp['active'] = 1;
                $getCategoryId = $list->id;
                $getCategoryData = $list;
            }

            $tempCategory[] = $temp;
        }

        $category = $tempCategory;
        if ($getCategoryId == 0) {
            if ($firstCategory > 0) {
                $category[0]['active'] = 1;
            }
            $getCategoryId = $firstCategory;
            $getCategoryData = $getCategoryDataTemp;
        }

        return [
            'data' => $category,
            'getCategoryId' => $getCategoryId,
            'getCategoryName' => $getCategoryData ? $getCategoryData->name : ''
        ];

    }

    private function getDoctorInfo($doctorId, $serviceId)
    {
        return Users::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor_service.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->join('doctor_service', 'doctor_service.doctor_id','=','doctor.id')
            ->where('doctor_service.service_id', '=', $serviceId)
            ->where('doctor.id', '=', $doctorId)
            ->where('users.doctor','=', 1)->first();
    }

    private function getUserAddress($userId)
    {
        $user = $this->request->attributes->get('_user');

        $getPhone = $user->phone ?? '';

        $logic = new SynapsaLogic();
        return $logic->getUserAddress($user->id, $getPhone);
    }

}
