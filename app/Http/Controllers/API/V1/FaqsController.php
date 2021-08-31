<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Faqs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FaqsController extends Controller
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

    public function getFaqs(){

        $user = $this->request->attributes->get('_user');

        $limit = 10;
        $data = Faqs::orderBy('id','DESC')->paginate($limit);

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }



}
