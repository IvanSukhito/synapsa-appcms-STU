<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Codes\Models\V1\Article;
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

        $limit = 10;

         
        $dataSliders = Sliders::orderBy('id','DESC')->paginate($limit);
        $dataProduct = Product::orderBy('id','DESC')->paginate($limit);
        $dataArticle = Article::orderBy('id','DESC')->paginate($limit);

       

        return response()->json([
            'success' => 1,
            'data' => [
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
