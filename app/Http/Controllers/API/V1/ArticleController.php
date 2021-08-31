<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\AccessLogin;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\DeviceToken;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\Article;
use App\Codes\Models\V1\ArticleCategory;
use App\Codes\Models\V1\ForgetPassword;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class ArticleController extends Controller
{
    protected $request;
    protected $accessLogin;
    protected $setting;


    public function __construct(Request $request, AccessLogin $accessLogin)
    {
        $this->request = $request;
        $this->accessLogin = $accessLogin;
        $this->setting = Cache::remember('setting', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
    }

    public function getArticle(){    
        
           // $user = $this->request->attributes->get('_user');

            //$token = JWTAuth::getToken();
            //dd($token);
            $data = Article::orderBy('id','DESC')->paginate(10);
    
            return response()->json([
            'success' => 1,
                'data' => $data,
                //'token' => $this->request->attributes->get('_refresh_token'),
            ]);
           
    }

  

}
