<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\BookNurse;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\SetJob;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\UsersAddress;
use App\Jobs\ProcessTransaction;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NurseController extends Controller
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

    public function bookNurse(){
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'date' => 'required',
            'shift_qty' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getDate = $this->request->get('date');
        $getQty = $this->request->get('shift_qty');

        $nurse = new BookNurse();
        $nurse->user_id = $user->id;
        $nurse->date_booked = $getDate;
        $nurse->shift_qty = $getQty;
        $nurse->save();

        return response()->json([
            'success' => 1,
            'data' => $nurse
        ]);
    }
    public function getReceiver($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = BookNurse::where('id',$id)->first();

        if(!$data){
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getReceiver = $user->fullname ?? '';
        $getAddress = $user->address ?? '';
        $getPhone = $user->phone ?? '';


        return response()->json([
            'success' => 1,
            'data' => [
                [
                    'receiver' => $getReceiver ?? '',
                    'address' => $getAddress ?? '',
                    'phone' => $getPhone ?? '',
                ]
            ]
        ]);
    }

    public function getAddress($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = BookNurse::where('id',$id)->first();

        if(!$data){
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => $this->getUserAddress($user->id)
        ]);
    }

    public function getSummary($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = BookNurse::where('id',$id)->first();

        if(!$data){
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $price = 50000;

        $total =  $data->shift_qty*$price;

        $data->total = $total;
        $data->save();

        $result = [
            'data' => $data,
            'total' => intval($total) > 0 ? number_format($total, 0, ',', '.') : 0
        ];

        return response()->json([
            'success' => 1,
            'data' => $result,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getPayment($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = BookNurse::where('id',$id)->first();

        if(!$data){
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }


        $getPayment = Payment::where('status', 80)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $data,
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
            'payment_id' => 'required|numeric',
            'receiver_name' => 'required',
            'receiver_address' => 'required',
            'receiver_phone' => 'required',
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

        $paymentId = $this->request->get('payment_id');

        $data = BookNurse::where('id',$id)->first();

        if(!$data){
            return response()->json([
                'success' => 0,
                'message' => ['Jadwal Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $receiver_name = $this->request->get('receiver_name');
        $receiver_address = $this->request->get('receiver_address');
        $receiver_phone = $this->request->get('receiver_phone');

        $getDetailsInformation = [
            'receiver_name' => $receiver_name ?? $user->fullname,
            'receiver_address' => $receiver_address ?? $user->address_detail,
            'receiver_phone' => $receiver_phone ?? $user->phone
        ];


        $price = 50000;
        $total = $data->shift_qty*$price;

        $getTotal = Transaction::where('klinik_id', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT).rand(100,199);

        $sendData = [
            'job' => [
                'code' => $newCode,
                'payment_id' => $paymentId,
                'user_id' => $user->id,
                'detail_info' => json_encode($getDetailsInformation),
                'type_service' => 'nurse',
                'schedule_id' => $data->id,
                'service_id' => 0,
                'nurse_info' => $data->toArray()
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
    }

    private function getUserAddress($userId)
    {
        $getUsersAddress = UsersAddress::where('user_id', $userId)->first();
        $user = $this->request->attributes->get('_user');

        $getAddressName = $getUsersAddress->address_name ?? $user->address ?? '';
        $getAddress = $getUsersAddress->address ?? $user->address_detail ?? '';
        $getCity = $getUsersAddress->city_id ?? '';
        $getCityName = $getUsersAddress->city_name ?? '';
        $getDistrict = $getUsersAddress->district_id ?? '';
        $getDistrictName = $getUsersAddress->district_name ?? '';
        $getSubDistrict = $getUsersAddress->sub_district_id ?? '';
        $getSubDistrictName = $getUsersAddress->sub_district_name ?? '';
        $getZipCode = $getUsersAddress->zip_code ?? '';
        $getPhone = $user->phone ?? '';

        return [
            'address_name' => $getAddressName,
            'address' => $getAddress,
            'city_id' => $getCity,
            'city_name' => $getCityName,
            'district_id' => $getDistrict,
            'district_name' => $getDistrictName,
            'sub_district_id' => $getSubDistrict,
            'sub_district_name' => $getSubDistrictName,
            'zip_code' => $getZipCode,
            'phone' => $getPhone
        ];

    }

}
