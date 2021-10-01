<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DoctorAppointmentController extends Controller
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
        $getDoctor = Doctor::where('user_id', $user->id)->first();

        $s = strip_tags($this->request->get('s'));
        $time = intval($this->request->get('time'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $dateNow = date('Y-m-d');

        switch ($time) {
            case 2 : $data = AppointmentDoctor::where('doctor_id', $getDoctor->id)->where('date', '=', $dateNow)->where('status', '!=', 99);
                break;
            case 3 : $data = AppointmentDoctor::where('doctor_id', $getDoctor->id)->where('date', '>', $dateNow)->where('status', '!=', 99);
                break;
            case 4 : $data = AppointmentDoctor::where('doctor_id', $getDoctor->id)->where('status', '=', 99);
                break;
            default: $data = AppointmentDoctor::where('doctor_id', $getDoctor->id)->where('date', '<', $dateNow)->where('status', '!=', 99);
                break;
        }
        if (strlen($s) > 0) {
            $data = $data->where('patient_name', 'LIKE', "%$s%");
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
        $getDoctor = Doctor::where('user_id', $user->id)->first();

        $data = AppointmentDoctor::where('doctor_id', $getDoctor->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
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

    public function meeting($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();

        $data = AppointmentDoctor::where('doctor_id', $getDoctor->id)->where('id', $id)->first();
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

    public function approveMeeting($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();

        $data = AppointmentDoctor::where('doctor_id', $getDoctor->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data->status = 2;
        $data->save();

        return response()->json([
            'success' => 1,
            'message' => ['Sukses Di Approve'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function cancelMeeting($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();

        $data = AppointmentDoctor::where('doctor_id', $getDoctor->id)->where('id', $id)->first();
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

    public function doctorMedicine($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();

        $data = AppointmentDoctor::where('doctor_id', $getDoctor->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $data = Product::where('klinik_id', $user->klinik_id);

        if (strlen($s) > 0) {
            $data = $data->where('name', 'LIKE', "%$s%");
        }

        $data = $data->orderBy('name','ASC')->paginate($getLimit);

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function doctorDiagnosis($id)
    {
        $user = $this->request->attributes->get('_user');
        $getDoctor = Doctor::where('user_id', $user->id)->first();

        $validator = Validator::make($this->request->all(), [
            'diagnosis' => 'required',
            'treatment' => 'required',
            'product_ids' => ''
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::where('doctor_id', $getDoctor->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getDoctorPrescription = $this->request->get('doctor_prescription');
        $listDoctorPrescription = [];
        foreach ($getDoctorPrescription as $listImage) {
            $image = base64_to_jpeg($listImage);
            $destinationPath = 'synapsaapps/users/'.$data->user_id.'/doctor/';
            $set_file_name = date('Ymd').'_'.md5('doctor_prescription'.strtotime('now').rand(0, 100)).'.jpg';
            $getFile = Storage::put($destinationPath.'/'.$set_file_name, $image);
            if ($getFile) {
                $getImage = env('OSS_URL').'/'.$destinationPath.'/'.$set_file_name;
                $listDoctorPrescription[] = $getImage;
            }
        }

        DB::beginTransaction();

        $getListProduct = $this->request->get('product_ids');
        $getListProductId = [];
        foreach ($getListProduct as $productId => $qty) {
            $getListProductId[] = $productId;
        }

        $getProducts = Product::whereIn('id', $getListProductId)->get();
        foreach ($getProducts as $list) {
            $getQty = isset($getListProduct[$list->id]) ? intval($getListProduct[$list->id]) : 1;
            AppointmentDoctorProduct::create([
                'appointment_doctor_id' => $id,
                'product_id' => $list->id,
                'product_name' => $list->name,
                'product_qty' => $getQty,
                'product_price' => $list->price,
                'choose' => 0,
                'status' => 1
            ]);
        }

        $data->diagnosis = strip_tags($this->request->get('diagnosis'));
        $data->treatment = strip_tags($this->request->get('treatment'));
        $data->doctor_prescription = json_encode($listDoctorPrescription);
        $data->status = 80;
        $data->save();

        DB::commit();

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

}
