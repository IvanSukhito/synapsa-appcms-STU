<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\UserLogic;
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

        $userLogic = new UserLogic();

        $getToken = $this->request->get('fcm_token');
        if ($getToken && strlen($getToken) > 5) {
            $userLogic->updateToken($user->id, $getToken);
        }

        $result = $userLogic->userInfo($user->id, $user);

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

        $totalNotif = Notifications::where('user_id',$user->id)->where('is_read', '=', 1)->count();
        $dataSliders = Sliders::where('status', '=', 80)->whereIn('klinik_id', [0, $user->klinik_id])->orderBy('id','DESC')->get();
        $dataProduct = Product::where('klinik_id', '=', $user->klinik_id)->orderBy('id','DESC')->paginate($limitProduct);
        $dataArticle = Article::where('klinik_id', '=', $user->klinik_id)
            ->where('publish_date', '<=', date('Y-m-d'))->where('publish_status', '=', 1)
            ->orderBy('publish_date','DESC')->limit($limitArticle)->get();

        $userLogic = new UserLogic();

        return response()->json([
            'success' => 1,
            'data' => [
                'user' => $userLogic->userInfo($user->id),
                'totalNotif' => $totalNotif,
                'sliders' => $dataSliders,
                'product' => $dataProduct,
                'article' => $dataArticle,
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

}
