<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\AppointmentLabDetails;
use App\Codes\Models\V1\Product;
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

        $s = strip_tags($this->request->get('s'));
        $time = intval($this->request->get('time'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $dateNow = date('Y-m-d');

        switch ($time) {
            case 2 : $data = AppointmentDoctor::where('user_id', $user->id)->where('date', '=', $dateNow)->where('status', '!=', 99);
                break;
            case 3 : $data = AppointmentDoctor::where('user_id', $user->id)->where('date', '>', $dateNow)->where('status', '!=', 99);
                break;
            case 4 : $data = AppointmentDoctor::where('user_id', $user->id)->where('status', '=', 99);
                break;
            default: $data = AppointmentDoctor::where('user_id', $user->id)->where('date', '<', $dateNow)->where('status', '!=', 99);
                break;
        }

        if (strlen($s) > 0) {
            $data = $data->where('doctor_name', 'LIKE', "%$s%");
        }
        $data = $data->orderBy('id','DESC')->paginate($getLimit);

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function detail($id)
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

        $formPatient = json_decode($data->form_patient, true);
        $doctorPrescription = json_decode($data->doctor_prescription, true);

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $data,
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
            'data' => $getDetails,
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
            ]);
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

        return response()->json([
            'success' => 1,
            'message' => ['Progress'],
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

        return response()->json([
            'success' => 1,
            'message' => ['Progress'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function checkout($id)
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'payment_id' => 'required|numeric',
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

        return response()->json([
            'success' => 1,
            'message' => ['Progress'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

}
