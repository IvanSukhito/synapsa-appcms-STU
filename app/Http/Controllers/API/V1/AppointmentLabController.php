<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentLab;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AppointmentLabController extends Controller
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

        $data = AppointmentLab::where('user_id', $user->id);
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

        $data = AppointmentLab::where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Lab Tidak Ditemukan'],
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

    public function cancelMeeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = AppointmentLab::where('user_id', $user->id)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Lab Tidak Ditemukan'],
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

}