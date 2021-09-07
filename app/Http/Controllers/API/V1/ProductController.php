<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\UsersCartDetail;
use App\Codes\Models\V1\UsersCart;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\Transaksi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use DB;

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
        $getUsersCart = UsersCart::where('users_id', $user->id)->first();

        $listProduct = Product::all();
        $listCart = DB::select(DB::raw("SELECT product_id, SUM(qty) AS total_qty FROM users_cart_detail
        WHERE users_cart_id = $getUsersCart->id GROUP BY product_id"));

       foreach ($listProduct as $list) {

        $temp = [];
        $totalPrice = 0;
        foreach($listCart as $data){
            $temp[] =
            [
                'name' => $list->name,
                'qty' => $data->total_qty,
                'price' => $list->price*$data->total_qty
            ];
            $totalPrice +=  $list->price*$data->total_qty;

        }

       }
       $listProduct = $temp;


        return response()->json([
            'success' => 1,
            'data' => $listProduct,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function storeCart(){
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
          'product_id' => 'required|numeric',
          'qty' => 'required|numeric',
        ]);
        $productId = $this->request->get('product_id');
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ], 422);
        }

        $product = Product::where('id', $productId)->first();
        if(!$product){
            return response()->json([
                'success' => 0,
                'message' => 'product not found',
            ], 422);
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
            ], 422);
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
            ], 422);
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
            ], 422);
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
            ], 422);
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
            ], 422);
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

        $user = $this->request->attributes->get('_user');
        $s = $this->request->get('s');

        $getData = Product::query();

        if ($s) {
            $getData = $getData->where('name', 'LIKE', strip_tags($s));
        }

        return response()->json([
            'success' => 1,
            'data' => $getData->paginate($this->limit),
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    Public function updateCart($id){

        $user = $this->request->attributes->get('_user');
        $getData = UsersCartDetail::where('id', $id)->first();

        $validator = Validator::make($this->request->all(), [
            'product_id' => 'required',
            'qty' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        if ($getData) {
            $getData->product_id = $this->request->get('product_id');
            $getData->qty = $this->request->get('qty');
            $getData->save();

            $dataProduct = Product::where('id', $getData->id)->first();
            return response()->json([
                'success' => 1,
                'data' => [
                    'product_name' => $dataProduct->name,
                    'product_price' => $dataProduct->price,
                    'jumlah' => $getData->qty,
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Success Update Cart'],
            ]);
        }
        else {

            return response()->json([
                'success' => 0,
                'message' => ['failed to update'],
                'token' => $this->request->attributes->get('_refresh_token')
            ], 422);
        }
    }

    public function deleteCart($id){

        $user = $this->request->attributes->get('_user');
        $getData = UsersCartDetail::findOrFail($id);

        if ($getData) {
            $getData->delete();
            return response()->json([
                'success' => 1,
                'message' => ['1 Product has been remove'],
                'token' => $this->request->attributes->get('_refresh_token'),

            ]);
        }
        else {
            return response()->json([
                'success' => 0,
                'message' => ['failed to remove'],
                'token' => $this->request->attributes->get('_refresh_token')
            ], 422);
        }
    }

    public function updatePaymentOld(){
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'payment' => 'required',
            'total_price' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }
        $getUsersCart = UsersCart::where('users_id', $user->id)->first();

        $getData = json_decode($getUsersCart->detail_information, true);


        $getInformation = [
            'receiver' => $getData['receiver'] ?? '',
            'address' => $getData['address'] ?? '',
            'phone' => $getData['phone'] ?? '',
            'payment' => $this->request->get('payment'),
            'total_price' => $this->request->get('total_price'),
        ];

        $getUsersCart->detail_information = $getInformation;
        $getUsersCart->save();

        $getData = ['detail_information' => $getInformation];


        return response()->json([
            'succes' => 1,
            'message' => 'Detail Payment Has Been Updated',
            'data' => $getData
        ]);
    }

    public function updatePayment(){
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'payment' => 'required',
            'payment_code' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getUsersCart = UsersCart::where('users_id', $user->id)->first();

        $getCart = UsersCartDetail::where('users_cart_id', $getUsersCart->id)->get();
        $listProduct = Product::all();
        $listCart = DB::select(DB::raw("SELECT product_id, SUM(qty) AS total_qty FROM users_cart_detail
        WHERE users_cart_id = $getUsersCart->id GROUP BY product_id"));

       foreach ($listProduct as $list) {

        $temp = [];
        $totalPrice = 0;
        foreach($listCart as $data){
            $temp[] =
            [
                'name' => $list->name,
                'price' => $list->price,
                'qty' => $data->total_qty,
                'total_price' => $list->price*$data->total_qty
            ];
            $totalPrice +=  $list->price*$data->total_qty;

        }

       }
       $listProduct = $temp;

       dd($listProduct);

        $getAddress = json_decode($getUsersCart->detail_address, true);
        $getShipping = json_decode($getUsersCart->detail_shipping, true);
        $getInformation = json_decode($getUsersCart->detail_information, true);

        $getShipping = $getShipping['name'] ?? '';
        $getShippingPrice = $getShipping['price'] ?? 0;
        $getCustomerName = $getInformation['receiver'] ?? '';


        $getAddress = [
            'address' => $getAddress['address'] ?? '',
            'address_detail' => $getAddress['address_detail'] ?? '',
            'city_id' => $getAddress['city_id'] ?? '',
            'district_id' => $getAddress['district_id'] ?? '',
            'sub_district_id' => $getAddress['sub_district_id'] ?? '',
            'zip_code' => $getAddress['zip_code'] ?? '',
        ];

        $trans = new Transaksi();
        $trans->payment  =  $this->request->get('payment');
        $trans->payment_code = $this->request->get('payment_code');
        $trans->total_price = $totalPrice + $getShippingPrice; //jumlah semua ditambah ongkir //
        $trans->customer_name = $getCustomerName;
        $trans->customer_address = json_encode($getAddress);
        $trans->shipping = $getShipping;
        $trans->list_order = json_encode($listProduct);
        $trans->status = 1;
        $trans->save();


        if($trans){
            $getUsersCart = UsersCart::where('users_id', $user->id)->first();
            $getUsersCart->delete();
            $getCart = UsersCartDetail::where('users_cart_id', $getUsersCart->id)->truncate();
        }


        //$getUsersCart->detail_information = $getInformation;
        //$getUsersCart->save();

        $getData = [
            'payment' => $trans->payment,
            'payment_code' => $trans->payment_code,
            'total_price' => $trans->total_price,
            'customer_name' => $trans->customer_name,
            'customer_address' => json_decode($trans->customer_address),
            'shipping' => $trans->shipping,
            'list_order' => json_decode($trans->list_order),
            'status_transaction' => $trans->status,
        ];
        return response()->json([
            'succes' => 1,
            'message' => 'Transaction Payment Already Created',
            'data' => $getData
        ]);
    }
}
