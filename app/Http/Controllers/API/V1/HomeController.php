<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Codes\Models\V1\Article;
use App\Codes\Models\V1\Notifications;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Sliders;

class HomeController extends Controller
{
    protected $request;
    protected $accessLogin;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function checkLogin()
    {
        $user = $this->request->attributes->get('_user');

        $data = [];

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function home()
    {
        $user = $this->request->attributes->get('_user');

        $data = [];

        $limit = 4;

        $totalNotif = Notifications::where('user_id',$user->id)->where('is_read',1)->count();
        $dataSliders = Sliders::orderBy('id','DESC')->get();
        $dataProduct = Product::orderBy('id','DESC')->paginate($limit);
        $dataArticle = Article::orderBy('publish_date','DESC')->where('publish_status', 1)->paginate($limit);       

        return response()->json([
            'success' => 1,
            'data' => [
                'user' => [
                    'klinik_id' => $user->klinik_id,
                    'fullname' => $user->fullname,
                    'address' => $user->address,
                    'address_detail' => $user->address_detail,
                    'zip_code' => $user->zip_code,
                    'gender' => $user->gender,
                    'dob' => $user->dob,
                    'nik' => $user->nik,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'patient' => $user->patient,
                    'doctor' => $user->doctor,
                    'nurse' => $user->nurse,
                    
                ],
                'totalNotif' => $totalNotif,
                'sliders' => $dataSliders,
                'product' => $dataProduct,
                'article' => $dataArticle,
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function notifications()
    {
        $user = $this->request->attributes->get('_user');

        $data = [];

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

}
