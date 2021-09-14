<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\LabCart;
use App\Codes\Models\V1\LabSchedule;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\TransactionDetails;
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
        $this->limit = 5;
        $this->setting = Cache::remember('setting', env('SESSION_LIFETIME'), function () {
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

        $service = Service::orderBy('orders', 'ASC')->get();

        $getInterestService = $serviceId > 0 ? $serviceId : $user->interest_service_id;
        $getServiceId = 0;
        $firstService = 0;
        $tempService = [];
        foreach ($service as $index => $list) {
            $temp[] = [
                'id' => $list->id,
                'name' => $list->name,
                'active' => 0
            ];

            if ($index == 0) {
                $firstService = $list->id;
            }
            if ($getInterestService == $list->id) {
                $temp['active'] = 1;
                $getServiceId = $list->id;
            }

            $tempService[] = $temp;

        }

        $service = $tempService;

        if ($getServiceId == 0) {
            if ($firstService > 0) {
                $service[0]['active'] = 1;
            }
            $getServiceId = $firstService;
        }

        $data = Lab::selectRaw('lab.id ,lab.name, lab_service.price, lab.image')
        ->join('lab_service', 'lab_service.lab_id','=','lab.id')
        ->where('lab_service.service_id','=', $getServiceId)
        ->where('lab.parent_id', '=', 0);

        if (strlen($s) > 0) {
            $data = $data->where('name', 'LIKE', "%$s%");
        }

        $data = $data->orderBy('name', 'ASC')->paginate($getLimit);

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'lab' => $data,
                'service' => $service
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getLabDetail($id){

        $user = $this->request->attributes->get('_user');

        $serviceId = intval($this->request->get('service_id'));

        $service = Service::orderBy('orders', 'ASC')->get();

        $getInterestService = $serviceId > 0 ? $serviceId : $user->interest_service_id;
        $getServiceId = 0;
        $firstService = 0;
        foreach ($service as $index => $list) {
            if ($index == 0) {
                $firstService = $list->id;
            }
            if ($getInterestService == $list->id) {
                $getServiceId = $list->id;
            }
        }

        if ($getServiceId == 0) {
            $getServiceId = $firstService;
        }

        $data = Lab::selectRaw('lab.parent_id, lab.name, lab.image, lab.desc_lab,lab.desc_benefit,
            lab.desc_preparation, lab.recommended_for, lab_service.price')
            ->join('lab_service', 'lab_service.lab_id','=','lab.id')
            ->where('lab_service.service_id','=', $getServiceId)
            ->where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        if ($data->parent_id == 0) {
            $dataLabTerkait = Lab::selectRaw('lab.id ,lab.name, lab_service.price, lab.image')
                ->join('lab_service', 'lab_service.lab_id','=','lab.id', 'LEFT')
                ->where('lab_service.service_id','=', $getServiceId)
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
        $getService = [];
        $getData = [];
        $total = 0;
        if ($getLabCart) {
            $getService = Service::where('id', $getLabCart->service_id)->first();
            $getData = Lab::selectRaw('lab_cart.id, lab.id AS lab_id, lab.parent_id ,lab.name, lab_service.price,
                lab.image, lab_cart.choose')
                ->join('lab_service', 'lab_service.lab_id','=','lab.id')
                ->join('lab_cart', 'lab_cart.lab_id','=','lab.id')
                ->where('lab_service.service_id', $getLabCart->service_id)
                ->where('user_id', $user->id)->get();
            foreach ($getData as $list) {
                $total += $list->price;
            }
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'cart' => $getData,
                'service' => $getService,
                'total' => $total,
                'total_nice' => number_format($total, 0, '.', '.')
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
        $getService = Service::where('id', $getServiceId)->first();
        $getData = Lab::selectRaw('lab_cart.id, lab.id AS lab_id, lab.parent_id ,lab.name, lab_service.price,
                lab.image, lab_cart.choose')
            ->join('lab_service', 'lab_service.lab_id','=','lab.id')
            ->join('lab_cart', 'lab_cart.lab_id','=','lab.id')
            ->where('lab_service.service_id', $getServiceId)
            ->where('user_id', $user->id)->get();
        foreach ($getData as $list) {
            $total += $list->price;
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'cart' => $getData,
                'service' => $getService,
                'total' => $total,
                'total_nice' => number_format($total, 0, '.', '.')
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
                'message' => ['Test Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
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
        foreach ($getData as $list) {
            if (in_array($list->id, $getCartIds)) {
                $list->choose = 1;
            }
            else {
                $list->choose = 0;
            }

            $list->save();
        }
        DB::commit();

        return response()->json([
            'success' => 1,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
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
                'message' => ['Test Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getLabSchedule = LabSchedule::where('service_id', $getData->service_id)->where('date_available', '=', $getDate)
            ->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'schedule_start' => date('Y-m-d', strtotime("+1 day")),
                'schedule_end' => date('Y-m-d', strtotime("+366 day")),
                'date' => $getDate,
                'schedule' => $getLabSchedule
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function scheduleAddress()
    {
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
        $user = $this->request->attributes->get('_user');

        $getCart = LabCart::where('user_id', '=', $user->id)->where('id', '=', $id)->where('choose', '=', 1)->first();
        if (!$getCart) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getLabSchedule = LabSchedule::where('service_id', $getCart->service_id)->where('id', '=', $id)
            ->where('book', '=', 80)->first();
        if (!$getLabSchedule) {
            return response()->json([
                'success' => 0,
                'message' => ['Schedule Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $total = 0;
        $getData = Lab::selectRaw('lab_cart.id, lab.id AS lab_id, lab.parent_id ,lab.name, lab_service.price,
                lab.image, lab_cart.choose')
            ->join('lab_service', 'lab_service.lab_id','=','lab.id')
            ->join('lab_cart', 'lab_cart.lab_id','=','lab.id')
            ->where('lab_service.service_id', $getCart->service_id)
            ->where('user_id', $user->id)->where('choose', 1)->get();
        foreach ($getData as $list) {
            $total += $list->price;
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'cart' => $getData,
                'schedule' => $getLabSchedule,
                'total' => $total,
                'total_nice' => number_format($total, 0, '.', '.')
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function getPayment($id)
    {
        $user = $this->request->attributes->get('_user');

        $getCart = LabCart::where('user_id', '=', $user->id)->where('id', '=', $id)->where('choose', '=', 1)->first();
        if (!$getCart) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getLabSchedule = LabSchedule::where('service_id', $getCart->service_id)->where('id', '=', $id)
            ->where('book', '=', 80)->first();
        if (!$getLabSchedule) {
            return response()->json([
                'success' => 0,
                'message' => ['Schedule Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $total = 0;
        $getData = Lab::selectRaw('lab_cart.id, lab.id AS lab_id, lab.parent_id ,lab.name, lab_service.price,
                lab.image, lab_cart.choose')
            ->join('lab_service', 'lab_service.lab_id','=','lab.id')
            ->join('lab_cart', 'lab_cart.lab_id','=','lab.id')
            ->where('lab_service.service_id', $getCart->service_id)
            ->where('user_id', $user->id)->where('choose', 1)->get();
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
                'total_nice' => number_format($total, 0, '.', '.'),
                'payment' => $getPayment
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function checkout($id)
    {
        $user = $this->request->attributes->get('_user');

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

        $paymentId = $this->request->get('payment_id');
        $scheduleId = $this->request->get('schedule_id');

        $getPayment = Payment::where('id', $paymentId)->where('status', 80)->first();
        $paymentInfo = [];
        if (!$getPayment) {
            return response()->json([
                'success' => 0,
                'message' => ['Payment Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getCart = LabCart::where('user_id', '=', $user->id)->where('id', '=', $id)->where('choose', '=', 1)->first();
        if (!$getCart) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getLabSchedule = LabSchedule::where('service_id', $getCart->service_id)->where('id', '=', $scheduleId)
            ->where('book', '=', 80)->first();
        if (!$getLabSchedule) {
            return response()->json([
                'success' => 0,
                'message' => ['Schedule Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $total = 0;
        $getData = Lab::selectRaw('lab_cart.id, lab.id AS lab_id, lab.parent_id ,lab.name, lab_service.price,
                lab.image, lab_cart.choose')
            ->join('lab_service', 'lab_service.lab_id','=','lab.id')
            ->join('lab_cart', 'lab_cart.lab_id','=','lab.id')
            ->where('lab_service.service_id', $getCart->service_id)
            ->where('user_id', $user->id)->where('choose', 1)->get();
        foreach ($getData as $list) {
            $total += $list->price;
        }

        $getUsersAddress = UsersAddress::where('user_id', $user->id)->first();

        switch ($getLabSchedule->service_id) {
            case 2 : $getType = 6; break;
            case 3 : $getType = 7; break;
            default : $getType = 5; break;
        }

        $extraInfo = [
            'service_id' => $getLabSchedule->service_id,
            'address_name' => $getUsersAddress->address_name ?? '',
            'address' => $getUsersAddress->address ?? '',
            'city_id' => $getUsersAddress->city_id ?? '',
            'district_id' => $getUsersAddress->district_id ?? '',
            'sub_district_id' => $getUsersAddress->sub_district_id ?? '',
            'zip_code' => $getUsersAddress->zip_code ?? '',
            'phone' => $user->phone ?? ''
        ];

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
            'subtotal' => $total,
            'total' => $total,
            'extra_info' => json_encode($extraInfo),
            'status' => 1
        ]);

        $listTransactionDetails = [];
        foreach ($getData as $list) {
            $getTransactionDetails = TransactionDetails::create([
                'transaction_id' => $getTransaction->id,
                'schedule_id' => $scheduleId,
                'lab_id' => $list->lab_id,
                'lab_name' => $list->name,
                'lab_price' => $list->price
            ]);
            $listTransactionDetails[] = $getTransactionDetails;
        }

        LabCart::where('user_id', $user->id)->where('choose', '=', 1)->delete();

        DB::commit();

        return response()->json([
            'success' => 1,
            'data' => [
                'checkout_info' => $getTransaction,
                'checkout_details' => $listTransactionDetails,
                'payment_info' => $paymentInfo
            ],
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

}
