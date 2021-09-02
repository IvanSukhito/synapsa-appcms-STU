<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\UsersCartDetail;
use App\Codes\Models\V1\UsersCart;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

    public function getCart(){
        $user = $this->request->attributes->get('_user');

        $limit = 10;
       
        $data = UsersCartDetail::selectRaw('product.name as product_name, product.price as product_price, users_cart_detail.qty as product_qty')
        ->join('users_cart','users_cart.id','=','users_cart_detail.users_cart_id')
        ->join('product','product.id','=','users_cart_detail.product_id')
        ->where('users_cart.users_id', $user->id);
       
        $getData = $data->limit($limit)->get();

        return response()->json([
            'success' => 1,
            'data' => $getData,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }
    
    public function storeCart(){
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
          'product_id' => 'required',
          'qty' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ]);
        }

            try{
            $getUsersCart = UsersCart::where('users_id', $user->id)->first();
        
            if(!$getUsersCart){

                $UsersCart = UsersCart::FirstOrCreate([
                    'users_id' => $user->id,
                ]);

                $cart = new UsersCartDetail();
                $cart->product_id = $this->request->get('product_id');
                $cart->users_cart_id = $UsersCart->id;
                $cart->qty = $this->request->get('qty');
                $cart->save();
            }
            else
            {
                $cart = new UsersCartDetail();
                $cart->product_id = $this->request->get('product_id');
                $cart->users_cart_id = $getUsersCart->id;
                $cart->qty = $this->request->get('qty');
                $cart->save();   
            }          

            $dataProduct = Product::where('id', $cart->product_id)->first();
            return response()->json([
                'message' => 'Data Has Been Inserted',
                'data' => [
                    'product_name' => $dataProduct->name,
                    'product_price' => $dataProduct->price,
                    'jumlah' => $cart->qty,
                ]
            ]);

        }

        catch (QueryException $e){
            return response()->json([
                'message' => 'Insert Failed'
            ], 500);
        }
    }

    public function updateReceiver(){
        $user = $this->request->attributes->get('_user');
        $getUsersCart = UsersCart::where('users_id', $user->id)->first();
        $validator = Validator::make($this->request->all(), [
            'receiver' => 'required',
            'address' => 'required',
            'phone' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

        $getInformation = [
            'receiver' => $this->request->get('receiver'),
            'address' => $this->request->get('address'),
            'phone' => $this->request->get('phone'),
        ];
        $getUsersCart->detail_information = $getInformation;
        $getUsersCart->save();

        $getData = ['detail_information' => $getInformation];


        return response()->json([
            'succes' => 1,
            'message' => 'Detail Information Has Been Updated',
            'data' => $getData
        ]);



    }
}
