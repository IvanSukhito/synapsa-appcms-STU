<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\UsersCartDetail;
use App\Codes\Models\V1\UsersCart;
use App\Codes\Models\V1\UsersAddress;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    protected $request;
    protected $setting;
    protected $limit;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setting = Cache::remember('setting', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
        $this->limit = 10;
    }

    public function getProduct(){

        $user = $this->request->attributes->get('_user');
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $data = Product::orderBy('id','DESC')->paginate($getLimit);

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

    public function getReceiver(){
        $user = $this->request->attributes->get('_user');
        $getData = UsersCart::where('users_id', $user->id)->first();

        if(!$getData){
            return response()->json([
                'success' => 0,
                'data' => $getData,
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

        $getData = json_decode($getData->detail_information, true);

        $getReceiver = $getData['receiver'] ?? '';
        $getAddress = $getData['address'] ?? '';
        $getPhone = $getData['phone'] ?? '';

        $getData = [
            'receiver' => $getData['receiver'] ?? '',
            'address' => $getData['address'] ?? '',
            'phone' => $getData['address'] ?? '',
        ];

        return response()->json([
            'succes' => 1,
            'data' => $getData
        ]);
    }

    public function getAddress(){
        $user = $this->request->attributes->get('_user');

        $getData = UsersCart::where('users_id', $user->id)->first();

        if(!$getData){

            $getDataUser = UsersAddress::where('user_id', $user->id)->first();
            $getData = json_decode($getDataUser->address_detail, true);
            $getData = [
                'address' => $getData['address'] ?? '',
                'address_detail' => $getData['address_detail'] ?? '',
                'city_id' => $getData['city_id'] ?? '',
                'district_id' => $getData['district_id'] ?? '',
                'sub_district_id' => $getData['sub_district_id'] ?? '',
                'zip_code' => $getData['zip_code'] ?? '',
            ];
            return response()->json([
                'success' => 0,
                'data' => $getData,
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

        $getData = json_decode($getData->detail_address, true);

        $getAddress = $getData['address'] ?? '';
        $getAddressDetail = $getData['address_detail'] ?? '';
        $getCity = $getData['city_id'] ?? '';
        $getDistrict = $getData['district_id'] ?? '';
        $getSubDistrict = $getData['sub_district_id'] ?? '';
        $getZipCode = $getData['zip_code'] ?? '';

        $getData = [
            'address' => $getData['address'] ?? '',
            'address_detail' => $getData['address_detail'] ?? '',
            'city_id' => $getData['city_id'] ?? '',
            'district_id' => $getData['district_id'] ?? '',
            'sub_district_id' => $getData['sub_district_id'] ?? '',
            'zip_code' => $getData['zip_code'] ?? '',
        ];

        return response()->json([
            'succes' => 1,
            'data' => $getData
        ]);
    }

    public function updateAddress(){
        $user = $this->request->attributes->get('_user');
        $getUsersCart = UsersCart::where('users_id', $user->id)->first();
        $validator = Validator::make($this->request->all(), [
            'city_id' => 'required',
            'district_id' => 'required',
            'sub_district_id' => 'required',
            'address' => 'required',
            'address_detail' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

        $getAddress = [
            'address' => $this->request->get('address'),
            'address_detail' => $this->request->get('address_detail'),
            'city_id' => $this->request->get('city_id'),
            'district_id' => $this->request->get('district_id'),
            'zip_code' => $this->request->get('zip_code'),
        ];
        $getUsersCart->detail_address = $getAddress;
        $getUsersCart->save();

        $getData = ['detail_address' => $getAddress];


        return response()->json([
            'succes' => 1,
            'message' => 'Detail Address Has Been Updated',
            'data' => $getData
        ]);
    }


    public function updateShipping(){

        $user = $this->request->attributes->get('_user');
        $getUsersCart = UsersCart::where('users_id', $user->id)->first();
        $validator = Validator::make($this->request->all(), [
            'name' => 'required',
            'price' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

        $getShipping = [
            'name' => $this->request->get('name'),
            'price' => $this->request->get('price'),
        ];
        //dd($getUsersCart);
        $getUsersCart->detail_shipping = $getShipping;
        $getUsersCart->save();

        $getData = ['detail_shipping' => $getShipping];


        return response()->json([
            'succes' => 1,
            'message' => 'Detail Shipping Has Been Updated',
            'data' => $getData
        ]);

    }

    public function getShipping(){

    }

    public function searchProduct(){
        $s = $this->request->get('s');

        $getData = Product::query();

        if ($s) {
            $getData = $getData->where('name', 'LIKE', strip_tags($s));
        }

        return response()->json([
            'success' => 1,
            'data' => $getData->paginate($this->limit)
        ]);
    }
}
