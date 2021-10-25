<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\V1\DeviceToken;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Users;
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
        $getKlinik = Klinik::where('id', $user->klinik_id)->first();

        $getToken = $this->request->get('fcm_token');
        if ($getToken && strlen($getToken) > 5) {
            $getUser = Users::where('id', $user->id)->first();
            $getDeviceToken = DeviceToken::firstOrCreate([
                'token' => $getToken
            ]);
            $getDeviceToken->getUser()->sync([$user->id]);
//            $getUser->getDeviceToken()->sync([$getDeviceToken->id]);
        }

        $result = [
            'klinik_id' => $user->klinik_id,
            'klinik_name' => $getKlinik ? $getKlinik->name : '',
            'fullname' => $user->fullname,
            'address' => $user->address,
            'address_detail' => $user->address_detail,
            'zip_code' => $user->zip_code,
            'gender' => intval($user->gender) == 1 ? 1 : 2,
            'phone' => $user->phone,
            'email' => $user->email,
            'patient' => $user->patient,
            'doctor' => $user->doctor,
            'nurse' => $user->nurse,
            'status' => $user->status,
            'status_nice' => $user->status_nice,
            'gender_nice' => $user->gender_nice
        ];

        if ($user->doctor == 1) {
            $getDoctor = Doctor::selectRaw('formal_edu, nonformal_edu, doctor_category_id, doctor_category.name AS doctor_category')
                ->join('doctor_category', 'doctor_category.id', '=', 'doctor.doctor_category_id')
                ->where('user_id', $user->id)->first();
            $result['info_doctor'] = $getDoctor;
        }

        return response()->json([
            'success' => 1,
            'data' => $result,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function home()
    {
        $user = $this->request->attributes->get('_user');

        $limitProduct = $this->request->get('limit_product');
        $limitArticle = $this->request->get('limit_article');
        if ($limitProduct <= 0) {
            $limitProduct = 4;
        }
        if ($limitArticle <= 0) {
            $limitArticle = 4;
        }

        $totalNotif = Notifications::where('user_id',$user->id)->where('is_read',1)->count();
        $dataSliders = Sliders::where('status',1)->orderBy('id','DESC')->get();
        $dataProduct = Product::orderBy('id','DESC')->paginate($limitProduct);
        $dataArticle = Article::orderBy('publish_date','DESC')->where('publish_status', 1)->limit($limitArticle)->get();
        $getKlinik = Klinik::where('id', $user->klinik_id)->first();

        return response()->json([
            'success' => 1,
            'data' => [
                'user' => [
                    'klinik_id' => $user->klinik_id,
                    'klinik_name' => $getKlinik ? $getKlinik->name : '',
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

}
