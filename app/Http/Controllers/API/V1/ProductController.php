<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\ProductCategory;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\UsersCartDetail;
use App\Codes\Models\V1\UsersCart;
use App\Codes\Models\V1\Transaksi;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $data = Product::selectRaw('id, name, image, unit, price, qty, stock, stock_flag');
        if (strlen($s) > 0) {
            $data = $data->where('name', 'LIKE', $s)->orWhere('desc', 'LIKE', $s);
        }
        $data = $data->orderBy('id','DESC')->paginate($getLimit);
        $category = ProductCategory::where('status', 80)->where('name', 'id')->toArray();

        return response()->json([
            'success' => 1,
            'data' => [
                'product' => $data,
                'category' => $category
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getProductDetail($id){
        $user = $this->request->attributes->get('_user');

        $data = Product::where('status', 80)->where('id', $id)->first();
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

        $getUsersCart = UsersCart::where('users_id', $user->id)->first();

        $listProduct = Product::selectRaw('product.id, product.name, product.image, product.price, product.unit, users_cart_detail.qty')
            ->join('users_cart_detail', 'users_cart_detail.product_id', '=', 'product.id')
            ->where('users_cart_detail.users_cart_id', '=', $getUsersCart->id);

        $totalQty = 0;
        $totalPrice = 0;
        foreach ($listProduct as $list) {
            $totalQty += $list->qty;
            $totalPrice += ($list->price * $list->qty);
       }

        return response()->json([
            'success' => 1,
            'data' => [
                'cart' => $listProduct,
                'total_qty' => $totalQty,
                'total_price' => $totalPrice,
            ],
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
            ], 404);
        }
        $productId = $this->request->get('product_id');
        $qty = $this->request->get('qty');

        try{

            $UsersCart = UsersCart::firstOrCreate([
                'users_id' => $user->id,
            ]);

            $cart = UsersCartDetail::firstOrCreate([
                'users_cart_id' => $UsersCart->id,
                'product_id' => $productId,
            ]);

            $cart->qty += $qty;
            $cart->save();

            return response()->json([
                'message' => 'Data Has Been Inserted',
                'data' => [
                    'product_id' => $productId,
                    'qty' => $cart->qty,
                ]
            ]);

        }

        catch (QueryException $e){
            return response()->json([
                'message' => 'Insert Failed'
            ], 500);
        }
    }

    Public function updateCart($id){

        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'qty' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ], 422);
        }

        $getQty = $this->request->get('qty');

        $getUsersCartDetail = UsersCartDetail::selectRaw('users_cart_detail.*')
            ->join('users_cart', 'users_cart.id', '=', 'users_cart_detail.users_cart_id')
            ->where('id', $id)->where('users_id', $user->id)->first();
        if (!$getUsersCartDetail) {
            return response()->json([
                'success' => 0,
                'message' => 'cart not found',
            ], 404);
        }

        $getUsersCartDetail->qty = $getQty;
        $getUsersCartDetail->save();

        return response()->json([
            'success' => 1,
            'data' => [
                'id' => $id,
                'product_id' => $getUsersCartDetail->product_id,
                'qty' => $getUsersCartDetail->qty,
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
            'message' => ['Success Update Cart'],
        ]);

    }

    public function deleteCart($id){

        $user = $this->request->attributes->get('_user');

        $getUsersCartDetail = UsersCartDetail::selectRaw('users_cart_detail.*')
            ->join('users_cart', 'users_cart.id', '=', 'users_cart_detail.users_cart_id')
            ->where('id', $id)->where('users_id', $user->id)->first();

        if ($getUsersCartDetail) {
            $getUsersCartDetail->delete();
            return response()->json([
                'success' => 1,
                'message' => ['Product has been remove'],
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

    public function getCartChooseProduct()
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'product_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ], 422);
        }

        $getListproductIds = $this->request->get('product_ids');
        if (!is_array($getListproductIds)) {
            $getListproductIds = [$getListproductIds];
        }

        DB::beginTransaction();

        UsersCartDetail::join('users_cart', 'users_cart.id', '=', 'users_cart_detail.users_cart_id')
            ->where('users_id', $user->id)
            ->whereIn('id', $getListproductIds)->update([
                'choose' => 0
            ]);

        UsersCartDetail::whereIn('id', $getListproductIds)->update([
            'choose' => 1
        ]);

        DB::commit();

        return response()->json([
            'success' => 1,
            'token' => $this->request->attributes->get('_refresh_token'),
            'message' => ['Success Choose Product'],
        ]);

    }

    public function getReceiver(){
        $user = $this->request->attributes->get('_user');

        $getData = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getData = json_decode($getData->detail_information, true);
        if ($getData) {
            $getReceiver = $getData['receiver'] ?? '';
            $getAddress = $getData['address'] ?? '';
            $getPhone = $getData['phone'] ?? '';
        }
        else {
            $getReceiver = $user->fullname ?? '';
            $getAddress = $user->address ?? '';
            $getPhone = $user->phone ?? '';
        }

        return response()->json([
            'success' => 1,
            'data' => [
                [
                    'receiver' => $getReceiver ?? '',
                    'address' => $getAddress ?? '',
                    'phone' => $getPhone ?? '',
                ]
            ]
        ]);
    }

    public function updateReceiver(){
        $user = $this->request->attributes->get('_user');
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

        $receiver = $this->request->get('receiver');
        $address = $this->request->get('address');
        $phone = $this->request->get('phone');

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getInformation = [
            'receiver' => $receiver,
            'address' => $address,
            'phone' => $phone,
        ];
        $getUsersCart->detail_information = json_encode($getInformation);
        $getUsersCart->save();

        $getData = ['detail_information' => $getInformation];


        return response()->json([
            'success' => 1,
            'message' => 'Detail Information Has Been Updated',
            'data' => $getData
        ]);
    }

    public function getAddress(){
        $user = $this->request->attributes->get('_user');

        $getData = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getData = json_decode($getData->detail_address, true);
        $getUsersAddress = UsersAddress::where('user_id', $user->id)->first();

        $getAddressName = $getData['address_name'] ?? $getUsersAddress->address_name;
        $getAddress = $getData['address'] ?? $getUsersAddress->address;
        $getCity = $getData['city_id'] ?? $getUsersAddress->city_id;
        $getDistrict = $getData['district_id'] ?? $getUsersAddress->district_id;
        $getSubDistrict = $getData['sub_district_id'] ?? $getUsersAddress->sub_district_id;
        $getZipCode = $getData['zip_code'] ?? $getUsersAddress->zip_code;

        return response()->json([
            'success' => 1,
            'data' => [
                'address_name' => $getAddressName,
                'address' => $getAddress,
                'city_id' => $getCity,
                'district_id' => $getDistrict,
                'sub_district_id' => $getSubDistrict,
                'zip_code' => $getZipCode,
            ]
        ]);
    }

    public function updateAddress(){
        $user = $this->request->attributes->get('_user');
        $validator = Validator::make($this->request->all(), [
            'address_name' => 'required',
            'address' => 'required',
            'city_id' => '',
            'district_id' => '',
            'sub_district_id' => '',
            'zip_code' => ''
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getAddressName = $this->request->get('address_name');
        $getAddress = $this->request->get('address');
        $getCity = $this->request->get('city_id');
        $getDistrict = $this->request->get('district_id');
        $getSubDistrict = $this->request->get('sub_district_id');
        $getZipCode = $this->request->get('zip_code');

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getDetailAddress = [
            'address_name' => $getAddressName,
            'address' => $getAddress,
            'city_id' => $getCity,
            'district_id' => $getDistrict,
            'sub_district_id' => $getSubDistrict,
            'zip_code' => $getZipCode,
        ];
        $getUsersCart->detail_address = json_encode($getDetailAddress);
        $getUsersCart->save();

        return response()->json([
            'success' => 1,
            'message' => 'Detail Address Has Been Updated',
            'data' => $getDetailAddress
        ]);
    }

    public function getShipping()
    {
        $user = $this->request->attributes->get('_user');
        $getData = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getDataShipping = Shipping::where('status', 80)->get();
        $getShipping = json_decode($getData->detail_shipping, true);

        return response()->json([
            'success' => 1,
            'data' => [
                'shipping' => $getDataShipping,
                'choose' => $getShipping ? $getShipping['shipping_id'] : 0
            ]
        ]);

    }

    public function updateShipping(){

        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'shipping_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ], 422);
        }

        $getShippingId = $this->request->get('shipping_id');
        $getShipping = Shipping::where('id', $getShippingId)->first();
        if (!$getShipping) {
            return response()->json([
                'success' => 0,
                'message' => ['Shipping Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getUsersCart->detail_shipping = json_encode([
            'shipping_id' => $getShippingId,
            'shipping_name' => $getShipping->name
        ]);

        $getUsersCart->save();

        return response()->json([
            'success' => 1,
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function cartSummary()
    {
        $user = $this->request->attributes->get('_user');

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getUsersCartDetails = Product::selectRaw('users_cart_detail.id, product.name AS product_name,
            product.name, product.image, product.unit, product.price, users_cart_detail.qty')
            ->join('users_cart_detail', 'users_cart_detail.product_id', '=', 'product.id')
            ->where('users_cart_detail.users_cart_id', '=', $getUsersCart->id)
            ->where('choose', 1)->get();

        $subTotal = 0;
        foreach ($getUsersCartDetails as $list) {
            $subTotal += ($list->qty * $list->price);
        }

        $getDetailsInformation = json_decode($getUsersCart->detail_information, true);
        $getDetailsShipping = json_decode($getUsersCart->detail_shipping, true);
        $shippingId = $getDetailsShipping['shipping_id'];
        $getShipping = Shipping::where('id', $shippingId)->first();
        if (!$getShipping) {
            return response()->json([
                'success' => 0,
                'message' => ['Shipping Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getShippingPrice = 15000;

        return response()->json([
            'success' => 1,
            'data' => [
                'cart_info' => [
                    'name' => $getDetailsInformation['receiver'] ?? '',
                    'address' => $getDetailsInformation['address'] ?? '',
                    'phone' => $getDetailsInformation['phone'] ?? '',
                    'shipping_name' => $getShipping->name,
                    'shipping_price' => $getShippingPrice,
                    'subtotal' => $subTotal,
                    'total' => $subTotal + $getShippingPrice
                ],
                'cart_details' => $getUsersCartDetails
            ],
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getPayment()
    {
        $user = $this->request->attributes->get('_user');

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getUsersCartDetails = Product::selectRaw('users_cart_detail.id, product.name AS product_name,
            product.name, product.image, product.unit, product.price, users_cart_detail.qty')
            ->join('users_cart_detail', 'users_cart_detail.product_id', '=', 'product.id')
            ->where('users_cart_detail.users_cart_id', '=', $getUsersCart->id)
            ->where('choose', 1)->get();

        $subTotal = 0;
        foreach ($getUsersCartDetails as $list) {
            $subTotal += ($list->qty * $list->price);
        }

        $getDetailsInformation = json_decode($getUsersCart->detail_information, true);
        $getDetailsShipping = json_decode($getUsersCart->detail_shipping, true);
        $shippingId = $getDetailsShipping['shipping_id'];
        $getShipping = Shipping::where('id', $shippingId)->first();
        if (!$getShipping) {
            return response()->json([
                'success' => 0,
                'message' => ['Shipping Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getShippingPrice = 15000;

        $getPayment = Payment::where('status', 80)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'cart_info' => [
                    'name' => $getDetailsInformation['receiver'] ?? '',
                    'address' => $getDetailsInformation['address'] ?? '',
                    'phone' => $getDetailsInformation['phone'] ?? '',
                    'shipping_name' => $getShipping->name,
                    'shipping_price' => $getShippingPrice,
                    'subtotal' => $subTotal,
                    'total' => $subTotal + $getShippingPrice
                ],
                'cart_details' => $getUsersCartDetails,
                'payment' => $getPayment
            ],
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function checkout()
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'payment_id' => 'required|numeric',
        ]);

        $paymentId = $this->request->get('payment_id');
        $getPayment = Payment::where('id', $paymentId)->first();

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getUsersCartDetails = Product::selectRaw('users_cart_detail.id, product.name AS product_name,
            product.name, product.image, product.unit, product.price, users_cart_detail.qty')
            ->join('users_cart_detail', 'users_cart_detail.product_id', '=', 'product.id')
            ->where('users_cart_detail.users_cart_id', '=', $getUsersCart->id)
            ->where('choose', 1)->get();

        $subTotal = 0;
        foreach ($getUsersCartDetails as $list) {
            $subTotal += ($list->qty * $list->price);
        }

        $getDetailsInformation = json_decode($getUsersCart->detail_information, true);
        $getDetailsShipping = json_decode($getUsersCart->detail_shipping, true);
        $shippingId = $getDetailsShipping['shipping_id'];
        $getShipping = Shipping::where('id', $shippingId)->first();
        if (!$getShipping) {
            return response()->json([
                'success' => 0,
                'message' => ['Shipping Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getShippingPrice = 15000;

        $paymentInfo = [];

        return response()->json([
            'success' => 1,
            'data' => [
                'cart_info' => [
                    'name' => $getDetailsInformation['receiver'] ?? '',
                    'address' => $getDetailsInformation['address'] ?? '',
                    'phone' => $getDetailsInformation['phone'] ?? '',
                    'shipping_name' => $getShipping->name,
                    'shipping_price' => $getShippingPrice,
                    'subtotal' => $subTotal,
                    'total' => $subTotal + $getShippingPrice
                ],
                'cart_details' => $getUsersCartDetails,
                'payment_info' => $paymentInfo
            ],
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

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
        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

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
            'success' => 1,
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

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

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

       //dd($listProduct);

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
            'success' => 1,
            'message' => 'Transaction Payment Already Created',
            'data' => $getData
        ]);
    }
}
