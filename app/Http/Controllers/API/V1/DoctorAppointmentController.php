<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $data = AppointmentDoctor::where('doctor_id', $user->id);
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

        $data = AppointmentDoctor::where('doctor_id', $user->id)->where('id', $id)->first();
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

        $data = AppointmentDoctor::where('doctor_id', $user->id)->where('id', $id)->first();
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

        $data = AppointmentDoctor::where('doctor_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data->status = 80;
        $data->save();

        return response()->json([
            'success' => 1,
            'message' => ['Sukses Di Batalkan'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function cancelMeeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = AppointmentDoctor::where('doctor_id', $user->id)->where('id', $id)->first();
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

    public function doctorDiagnosis($id)
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'diagnosis' => 'required',
            'treatment' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $data = AppointmentDoctor::where('doctor_id', $user->id)->where('id', $id)->first();
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

        $data->diagnosis = strip_tags($this->request->get('diagnosis'));
        $data->treatment = strip_tags($this->request->get('treatment'));
        $data->doctor_prescription = json_encode($listDoctorPrescription);
        $data->save();

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

}
