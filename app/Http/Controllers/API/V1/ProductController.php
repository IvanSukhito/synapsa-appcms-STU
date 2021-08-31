<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
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

    public function getProduct(){

        $user = $this->request->attributes->get('_user');

        $limit = 10;
        $data = Product::orderBy('id','DESC')->paginate($limit);

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getProductDetail($id){
        $user = $this->request->attributes->get('_user');

        $data = Product::where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'data' => $data,
                'message' => 'Product Not Found',
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

}
