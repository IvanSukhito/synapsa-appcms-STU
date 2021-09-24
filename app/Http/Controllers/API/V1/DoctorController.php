<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\UsersAddress;
use Illuminate\Support\Facades\DB;
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

        $getServiceData = $this->getService($getInterestService);
        $getCategoryData = $this->getCategory($getInterestCategory);

        $data = Users::selectRaw('doctor.id, users.fullname as doctor_name, image, doctor_service.price, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->join('doctor_service', 'doctor_service.doctor_id','=','doctor.id')
            ->where('doctor.doctor_category_id','=', $getCategoryData['getCategoryId'])
            ->where('doctor_service.service_id','=', $getServiceData['getServiceId'])
            ->where('users.doctor','=', 1);

        if (strlen($s) > 0) {
            $data = $data->where('users.fullname', 'LIKE', "%$s%");
        }

        $data = $data->orderBy('users.fullname', 'ASC')->paginate($getLimit);

        return response()->json([
            'success' => 1,
            'data' => [
                'doctor' => $data,
                'service' => $getServiceData['data'],
                'active' => [
                    'service' => $getServiceData['getServiceId'],
                    'service_name' => $getServiceData['getServiceName'],
                    'category' => $getCategoryData['getCategoryId'],
                    'category_name' => $getCategoryData['getCategoryName']
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
        $getService = Service::where('id', $serviceId)->first();
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

        $getService = Service::where('id', $serviceId)->first();
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

            $getDoctorSchedule = DoctorSchedule::where('doctor_id', '=', $id)->where('service_id', '=', $serviceId)
                ->where('date_available', '=', $getDate)
                ->get();

            return response()->json([
                'success' => 1,
                'data' => [
                    'schedule_start' => date('Y-m-d', strtotime("+1 day")),
                    'schedule_end' => date('Y-m-d', strtotime("+366 day")),
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

        return response()->json([
            'success' => 1,
            'data' => [
                'schedule' => $getDoctorSchedule,
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function scheduleAddress(){
        $user = $this->request->attributes->get('_user');

        $getUsersAddress = UsersAddress::where('user_id', $user->id)->first();

        $getAddressName = $getUsersAddress->address_name ?? '';
        $getAddress = $getUsersAddress->address ?? '';
        $getCity = $getUsersAddress->city_id ?? '';
        $getDistrict = $getUsersAddress->district_id ?? '';
        $getSubDistrict = $getUsersAddress->sub_district_id ?? '';
        $getZipCode = $getUsersAddress->zip_code ?? '';
        $getPhone = $user->phone ?? '';

        return response()->json([
            'success' => 1,
            'data' => [
                'address_name' => $getAddressName,
                'address' => $getAddress,
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

        $getService = Service::where('id', $getDoctorSchedule->service_id)->first();

        $data = Users::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor_service.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->join('doctor_service', 'doctor_service.doctor_id','=','doctor.id')
            ->where('doctor.id', '=', $getDoctorSchedule->doctor_id)
            ->where('doctor_service.service_id', '=', $getDoctorSchedule->service_id)
            ->where('users.doctor','=', 1)->first();

        return response()->json([
            'success' => 1,
            'data' => [
                'schedule' => $getDoctorSchedule,
                'doctor' => $data,
                'service' => $getService
            ],
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

        $data = Users::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor_service.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->join('doctor_service', 'doctor_service.doctor_id','=','doctor.id')
            ->where('doctor.id', '=', $getDoctorSchedule->doctor_id)
            ->where('doctor_service.service_id', '=', $getDoctorSchedule->service_id)
            ->where('users.doctor','=', 1)->first();

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

        $validator = Validator::make($this->request->all(), [
            'payment_id' => 'required|numeric'
        ]);
        $validator->setAttributeNames([
            'payment_id' => 'Pembayaran Harus Diisi Dengan Angka',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $paymentId = $this->request->get('payment_id');

        $getUsersAddress = UsersAddress::where('user_id', $user->id)->first();

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

        switch ($getDoctorSchedule->service_id) {
            case 2 : $getType = 3; break;
            case 3 : $getType = 4; break;
            default : $getType = 2; break;
        }

        $extraInfo = [
            'service_id' => $getDoctorSchedule->service_id,
            'address_name' => $getUsersAddress->address_name ?? '',
            'address' => $getUsersAddress->address ?? '',
            'city_id' => $getUsersAddress->city_id ?? '',
            'district_id' => $getUsersAddress->district_id ?? '',
            'sub_district_id' => $getUsersAddress->sub_district_id ?? '',
            'zip_code' => $getUsersAddress->zip_code ?? '',
            'phone' => $user->phone ?? ''
        ];

        $data = Users::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor_service.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->join('doctor_service', 'doctor_service.doctor_id','=','doctor.id')
            ->where('doctor.id', '=', $getDoctorSchedule->doctor_id)
            ->where('doctor_service.service_id', '=', $getDoctorSchedule->service_id)
            ->where('users.doctor','=', 1)->first();

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Doktor Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        $getPayment = Payment::where('id', $paymentId)->first();
        $paymentInfo = [];

        $subTotal = $data->price;
        $total = $subTotal;

        $getTotal = Transaction::where('klinik_id', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = date('Ym').str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT);

        DB::beginTransaction();

        $getTransaction = Transaction::create([
            'klinik_id' => $user->klinik_id,
            'user_id' => $user->id,
            'code' => $newCode,
            'payment_id' => $paymentId,
            'payment_name' => $getPayment->name,
            'type' => $getType,
            'subtotal' => $subTotal,
            'total' => $total,
            'extra_info' => json_encode($extraInfo),
            'status' => 1
        ]);

        $getTransactionDetails = TransactionDetails::create([
            'transaction_id' => $getTransaction->id,
            'schedule_id' => $id,
            'doctor_id' => $data->id,
            'doctor_name' => $data->doctor_name,
            'doctor_price' => $subTotal
        ]);

        DoctorSchedule::where('id', $id)->update([
            'book' => 99
        ]);

        DB::commit();

        return response()->json([
            'success' => 1,
            'data' => [
                'checkout_info' => $getTransaction,
                'checkout_details' => $getTransactionDetails,
                'payment_info' => $paymentInfo
            ],
            'message' => ['Berhasil'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    private function getService($getInterestService) {

        $getServiceDoctor = isset($this->setting['service-doctor']) ? json_decode($this->setting['service-doctor'], true) : [];
        if (count($getServiceDoctor) > 0) {
            $service = Service::whereIn('id', $getServiceDoctor)->orderBy('orders', 'ASC')->get();
        }
        else {
            $service = Service::orderBy('orders', 'ASC')->get();
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

}
