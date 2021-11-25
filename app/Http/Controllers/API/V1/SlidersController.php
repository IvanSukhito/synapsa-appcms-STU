<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Sliders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SlidersController extends Controller
{
    protected $request;
    protected $setting;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
    }

    public function getSliders(){

        $user = $this->request->attributes->get('_user');

        $limit = 10;
        $data = Sliders::where('status',1)->whereIn('klinik_id', [0, $user->klinik_id])->orderBy('id','DESC')->paginate($limit);

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }



}
