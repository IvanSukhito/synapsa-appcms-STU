<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\Service;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $service = Service::orderBy('orders', 'ASC')->get();

        $getInterestService = $user->interest_service_id;
        if ($getInterestService <= 0) {
            foreach ($service as $index => $list) {
                if ($index == 0) {
                    $getInterestService = $list->id;
                }
            }
        }

        $data = Lab::selectRaw('lab.id ,lab.name, lab.price, lab.image')
        ->join('lab_service', 'lab_service.lab_id','=','lab.id')
        ->where('lab_service.service_id','=', $getInterestService)
        ->where('lab.parent_id', '=', 0);

        if (strlen($s) > 0) {
            $data = $data->where('name', 'LIKE', "%$s%");
        }

        $data = $data->orderBy('name', 'ASC')->paginate($getLimit);

        if (!$data) {
            return response()->json([
                'success' => 0,
                'data' => $data,
                'message' => ['Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'lab' => $data,
                'service' => $service,
                'active' => [
                    'service' => $getInterestService,
                ]
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getLabDetail($id){
        $user = $this->request->attributes->get('_user');

        $data = Lab::where('id', $id)->first();

        $parentId = $data ? $data->id : 0;
        $dataLabTerkait = Lab::where('parent_id', $data->id)->get();

        $getData = [
          'Lab' => $data,
          'Lab Terkait' => $dataLabTerkait
        ];

        if (!$data) {
            return response()->json([
                'success' => 0,
                'data' => $data,
                'message' => ['Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else {
            return response()->json([
                'success' => 1,
                'data' => $getData,
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

    }
    public function getCart(){
        $user = $this->request->attributes->get('_user');
        $labId = $this->request->get('lab_id');

        $data = Lab::selectRaw('lab.id ,lab.name, lab.price, lab.image')
        ->join('lab_service', 'lab_service.lab_id','=','lab.id')
        ->whereIn('lab.id', $labId)
        ->get();

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else {
            return response()->json([
                'success' => 1,
                'data' => [
                    'lab' => $data
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

    }
   
}
