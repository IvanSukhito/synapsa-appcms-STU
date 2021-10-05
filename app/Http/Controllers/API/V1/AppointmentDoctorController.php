<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\AppointmentLabDetails;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AppointmentDoctorController extends Controller
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
        $this->limit = 10;
    }

    public function index()
    {
        $user = $this->request->attributes->get('_user');

//        $s = strip_tags($this->request->get('s'));
        $time = intval($this->request->get('time'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $dateNow = date('Y-m-d');

        $data = AppointmentDoctor::selectRaw('appointment_doctor.id, appointment_doctor.doctor_id AS janji_id,
            appointment_doctor.doctor_name AS janji_name, appointment_doctor.type_appointment, appointment_doctor.date,
            appointment_doctor.time_start, appointment_doctor.time_end, appointment_doctor.status,
            doctor_category.name AS doctor_category, users.image AS image,
            CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
            ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->where('appointment_doctor.user_id', $user->id)
//            ->where('date', '=', $dateNow)
            ->where('appointment_doctor.status', '!=', 99)
            ->union(
                AppointmentLab::selectRaw('appointment_lab.id, 0 AS janji_id,
                    0 AS janji_name, appointment_lab.type_appointment, appointment_lab.date,
                    appointment_lab.time_start, appointment_lab.time_end, appointment_lab.status,
                    0 AS doctor_category, 0 AS image,
                    CONCAT("'.env('OSS_URL').'/'.'", 0) AS image_full')
                ->where('user_id', $user->id)
//                ->where('date', '=', $dateNow)
                ->where('status', '!=', 99)
            );

//        switch ($time) {
//            case 2 : $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image, CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
//                ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
//                ->join('users', 'users.id', '=', 'doctor.user_id')
//                ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
//                ->where('appointment_doctor.user_id', $user->id)
//                ->where('date', '=', $dateNow)
//                ->where('appointment_doctor.status', '!=', 99);
//                break;
//            case 3 : $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image, CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
//                ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
//                ->join('users', 'users.id', '=', 'doctor.user_id')
//                ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
//                ->where('appointment_doctor.user_id', $user->id)
//                ->where('appointment_doctor.date', '>', $dateNow)
//                ->where('appointment_doctor.status', '!=', 99);
//                break;
//            case 4 : $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image, CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
//                ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
//                ->join('users', 'users.id', '=', 'doctor.user_id')
//                ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
//                ->where('appointment_doctor.user_id', $user->id)
//                ->where('appointment_doctor.status', '=', 99);
//                break;
//            default: $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image, CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
//                ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
//                ->join('users', 'users.id', '=', 'doctor.user_id')
//                ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
//                ->where('appointment_doctor.user_id', $user->id)
//                ->where('date', '<', $dateNow)
//                ->where('appointment_doctor.status', '!=', 99);
//                break;
//        }

//        if (strlen($s) > 0) {
//            $data = $data->where('doctor_name', 'LIKE', "%$s%");
//        }
        $data = $data->orderBy('date','DESC')->orderBy('time_start','ASC')->paginate($getLimit);

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function detail($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = AppointmentDoctor::selectRaw('appointment_doctor.*, doctor_category.name, users.image, CONCAT("'.env('OSS_URL').'/'.'", users.image) AS image_full')
            ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
            ->join('users', 'users.id', '=', 'doctor.user_id')
            ->join('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->where('appointment_doctor.user_id', $user->id)
            ->where('appointment_doctor.id', $id)
            ->first();

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $formPatient = json_decode($data->form_patient, true);
        $doctorPrescription = json_decode($data->doctor_prescription, true);

        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty, appointment_doctor_product.choose')
            ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $data,
                'product' => $getDetails,
                'form_patient' => $formPatient,
                'doctor_prescription' => $doctorPrescription
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function fillForm($id)
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'body_height' => 'numeric',
            'body_weight' => 'numeric',
            'blood_pressure' => '',
            'body_temperature' => 'numeric',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getMedicalCheckup = $this->request->get('medical_checkup');
        $listMedicalCheckup = [];
        foreach ($getMedicalCheckup as $listImage) {
            $image = base64_to_jpeg($listImage);
            $destinationPath = 'synapsaapps/users/'.$user->id.'/forms/';
            $set_file_name = date('Ymd').'_'.md5('medical_checkup'.strtotime('now').rand(0, 100)).'.jpg';
            $getFile = Storage::put($destinationPath.'/'.$set_file_name, $image);
            if ($getFile) {
                $getImage = env('OSS_URL').'/'.$destinationPath.'/'.$set_file_name;
                $listMedicalCheckup[] = $getImage;
            }
        }

        $saveFormPatient = [
            'body_height' => strip_tags($this->request->get('body_height')),
            'body_weight' => strip_tags($this->request->get('body_weight')),
            'blood_pressure' => strip_tags($this->request->get('blood_pressure')),
            'body_temperature' => strip_tags($this->request->get('body_temperature')),
            'medical_checkup' => $listMedicalCheckup
        ];

        $data->form_patient = json_encode($saveFormPatient);
        $data->save();

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function meeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = AppointmentDoctor::where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'message' => ['Progress'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function cancelMeeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = AppointmentDoctor::whereIn('status', [1,2,80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data->status = 99;
        $data->save();

        return response()->json([
            'success' => 1,
            'message' => ['Sukses Di Batalkan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function cart($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty, appointment_doctor_product.choose')
            ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $data,
                'product' => $getDetails
            ],
            'message' => ['Sukses'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function chooseCart($id)
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'cart_ids' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getCartIds = $this->request->get('cart_ids');

        DB::beginTransaction();

        $getUsersCartDetails = AppointmentDoctorProduct::selectRaw('appointment_doctor_product.id, product_id, choose')
            ->join('appointment_doctor', 'appointment_doctor.id', '=', 'appointment_doctor_product.appointment_doctor_id')
            ->where('users_id', $user->id)->get();

        $haveProduct = 0;
        if ($getUsersCartDetails) {
            foreach ($getUsersCartDetails as $getUsersCartDetail) {
                if (in_array($getUsersCartDetail->product_id, $getCartIds)) {
                    $haveProduct = 1;
                    $getUsersCartDetail->choose = 1;
                }
                else {
                    $getUsersCartDetail->choose = 0;
                }
                $getUsersCartDetail->save();
            }
        }

        DB::commit();

        if ($haveProduct == 1) {
            return response()->json([
                'success' => 1,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Berhasil Memilih Produk'],
            ]);
        }
        else {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Tidak ada Produk yang di pilih'],
            ], 422);
        }

    }

    public function shipping($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty, appointment_doctor_product.choose')
            ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)->get();

        if ($getDetails->count() <= 0) {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Tidak ada Produk yang di pilih'],
            ], 422);
        }

        $getShipping = Shipping::where('status', 80)->orderBy('orders', 'ASC')->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $data,
                'product' => $getDetails,
                'shipping' => $getShipping
            ],
            'message' => ['Berhasil'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function payment($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty, appointment_doctor_product.choose')
            ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)->get();

        if ($getDetails->count() <= 0) {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Tidak ada Produk yang di pilih'],
            ], 422);
        }

        $getPayment = Payment::where('status', 80)->orderBy('orders', 'ASC')->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $data,
                'product' => $getDetails,
                'payment' => $getPayment
            ],
            'message' => ['Berhasil'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function checkout($id)
    {
        $user = $this->request->attributes->get('_user');

        $needPhone = 0;
        $validator = Validator::make($this->request->all(), [
            'shipping_id' => 'required|numeric',
            'payment_id' => 'required|numeric',
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

        $data = AppointmentDoctor::whereIn('status', [80])->where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getShippingPrice = 15000;

        $total = 0;
        $getDetails = Product::selectRaw('appointment_doctor_product.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, appointment_doctor_product.product_qty, appointment_doctor_product.choose')
            ->join('appointment_doctor_product', 'appointment_doctor_product.product_id', '=', 'product.id')
            ->where('appointment_doctor_product.appointment_doctor_id', '=', $id)->get();
        foreach ($getDetails as $list) {
            $total += $list->price;
        }

        $total += $getShippingPrice;

        $getTotal = Transaction::where('klinik_id', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT).rand(100,199);

        $sendData = [
            'job' => [
                'code' => $newCode,
                'payment_id' => $paymentId,
                'user_id' => $user->id,
                'type_service' => 'product_klinik',
                'service_id' => 0
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

}
