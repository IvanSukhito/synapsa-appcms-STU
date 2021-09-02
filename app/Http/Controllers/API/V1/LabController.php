<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LabController extends Controller
{
    protected $request;
    protected $setting;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setting = Cache::remember('setting', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
    }

    public function getLab(){

        $user = $this->request->attributes->get('_user');

        $limit = 10;
       
      
        $data = Lab::where('parent_id', 0)->limit($limit)->get();

     

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getLabDetail($id){
        $user = $this->request->attributes->get('_user');

        $data = Lab::where('id', $id)->first();

        $parentId = $data ? $data->id : '';
        $dataLabTerkait = Lab::where('parent_id', $parentId)->get();

        $getData = [    
          'Lab' => $data,
          'Lab Terkait' => $dataLabTerkait
        ];

        if (!$data) {
            return response()->json([
                'success' => 0,
                'data' => $data,
                'message' => 'Doctor Not Found',
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

}
