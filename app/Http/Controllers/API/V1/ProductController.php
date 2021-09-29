<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\ProductCategory;
use App\Codes\Models\V1\SetJob;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\UsersCartDetail;
use App\Codes\Models\V1\UsersCart;
use App\Codes\Models\V1\Transaction;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessTransaction;
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
        $this->setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
        $this->limit = 10;
    }

    public function getProduct()
    {
        $user = $this->request->attributes->get('_user');

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $data = Product::selectRaw('id, name, image, unit, price, stock, stock_flag')->where('klinik_id', '=', 0);
        if (strlen($s) > 0) {
            $data = $data->where('name', 'LIKE', strip_tags($s))->orWhere('desc', 'LIKE', strip_tags($s));
        }
        $data = $data->where('status', 80)->orderBy('id','DESC')->paginate($getLimit);
        $category = ProductCategory::where('status', 80)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'product' => $data,
                'category' => $category
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getProductRujukan()
    {
        $user = $this->request->attributes->get('_user');

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $data = Product::selectRaw('id, name, image, unit, price, stock, stock_flag')->where('klinik_id', '=', $user->klinik_id);
        if (strlen($s) > 0) {
            $data = $data->where('name', 'LIKE', strip_tags($s))->orWhere('desc', 'LIKE', strip_tags($s));
        }
        $data = $data->where('status', 80)->where('klinik_id', $user->klinik_id)->orderBy('id','DESC')->paginate($getLimit);
        $category = ProductCategory::where('status', 80)->get();


        if(!$data){
            return response()->json([
                'success' => 0,
                'message' => ['Produk Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else{
            return response()->json([
                'success' => 1,
                'data' => [
                    'product' => $data,
                    'category' => $category
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }


    }

    public function getProductDetail($id)
    {
        $user = $this->request->attributes->get('_user');

        $data = Product::where('status', 80)->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Produk Tidak Ditemukan'],
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

    public function getCart()
    {
        $user = $this->request->attributes->get('_user');

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $listProduct = Product::selectRaw('users_cart_detail.id, product.id as product_id, product.name, product.image,
            product.price, product.unit, users_cart_detail.qty, users_cart_detail.choose')
            ->join('users_cart_detail', 'users_cart_detail.product_id', '=', 'product.id')
            ->where('users_cart_detail.users_cart_id', '=', $getUsersCart->id)->get();

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
                'total_qty_nice' => number_format($totalQty, 0, ',', '.'),
                'total_price' => $totalPrice,
                'total_price_nice' => number_format($totalPrice, 0, ',', '.'),
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function storeCart()
    {
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
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $product = Product::where('id', $productId)->first();
        if(!$product){
            return response()->json([
                'success' => 0,
                'message' => ['Produk Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
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
                'message' => ['Data Berhasil Dimasukan'],
                'data' => [
                    'product_id' => $productId,
                    'qty' => $cart->qty,
                    'qty_nice' => number_format($cart->qty, 0, ',', '.'),
                ]
            ]);

        }

        catch (QueryException $e){
            return response()->json([
                'message' => ['Gagal Memasukan']
            ], 500);
        }
    }

    Public function updateCart($id)
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'qty' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getQty = $this->request->get('qty');

        $getUsersCartDetail = UsersCartDetail::selectRaw('users_cart_detail.*')
            ->join('users_cart', 'users_cart.id', '=', 'users_cart_detail.users_cart_id')
            ->where('users_cart_detail.id', $id)->where('users_cart.users_id', $user->id)->first();
        if (!$getUsersCartDetail) {
            return response()->json([
                'success' => 0,
                'message' => ['Keranjang Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
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
                'qty_nice' => number_format($getUsersCartDetail->qty, 0, ',', '.'),
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
            'message' => ['Berhasil Memperbarui Keranjang'],
        ]);

    }

    public function deleteCart($id)
    {

        $user = $this->request->attributes->get('_user');

        $getUsersCartDetail = UsersCartDetail::selectRaw('users_cart_detail.*')
            ->join('users_cart', 'users_cart.id', '=', 'users_cart_detail.users_cart_id')
            ->where('users_cart_detail.id', $id)->where('users_cart.users_id', $user->id)->first();

        if ($getUsersCartDetail) {
            $getUsersCartDetail->delete();
            return response()->json([
                'success' => 1,
                'message' => ['Produk berhasil dihapus'],
                'token' => $this->request->attributes->get('_refresh_token'),

            ]);
        }
        else {
            return response()->json([
                'success' => 0,
                'message' => ['Gagal Menghapus'],
                'token' => $this->request->attributes->get('_refresh_token')
            ], 422);
        }
    }

    public function postCartChooseProduct()
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'product_ids' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getListproductIds = $this->request->get('product_ids');
        if (!is_array($getListproductIds)) {
            $getListproductIds = [$getListproductIds];
        }

        DB::beginTransaction();

        $getUsersCartDetails = UsersCartDetail::selectRaw('users_cart_detail.id, product_id, choose, users_id')
            ->join('users_cart', 'users_cart.id', '=', 'users_cart_detail.users_cart_id')
            ->where('users_id', $user->id)->get();

        $haveProduct = 0;
        if ($getUsersCartDetails) {
            foreach ($getUsersCartDetails as $getUsersCartDetail) {
                if (in_array($getUsersCartDetail->product_id, $getListproductIds)) {
                    $haveProduct = 1;
                    $getUsersCartDetail->choose = 1;
                }
                else {
                    $getUsersCartDetail->choose = 0;
                }
                $getUsersCartDetail->save();
            }
        }

        DB::commit();

        if ($haveProduct == 1) {
            return response()->json([
                'success' => 1,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Berhasil Memilih Produk'],
            ]);
        }
        else {
            return response()->json([
                'success' => 0,
                'token' => $this->request->attributes->get('_refresh_token'),
                'message' => ['Tidak ada Produk yang di pilih'],
            ]);
        }

    }

    public function getReceiver()
    {
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

    public function updateReceiver()
    {
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
            'message' => ['Detail Informasi Berhasil Diperbarui'],
            'data' => $getData
        ]);
    }

    public function getAddress()
    {
        $user = $this->request->attributes->get('_user');

        $getData = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getUsersAddress = UsersAddress::where('user_id', $user->id)->first();

        $getAddressName = $getUsersAddress ? $getUsersAddress->address_name : '';
        $getAddress = $getUsersAddress ? $getUsersAddress->address : '';
        $getCity = $getUsersAddress ? $getUsersAddress->city_id : '';
        $getDistrict = $getUsersAddress ? $getUsersAddress->district_id : '';
        $getSubDistrict = $getUsersAddress ? $getUsersAddress->sub_district_id : '';
        $getZipCode = $getUsersAddress ? $getUsersAddress->zip_code : '';

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

    public function getShipping()
    {
        $user = $this->request->attributes->get('_user');
        $getData = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getDataShipping = Shipping::where('status', 80)->get();
        $getShipping = json_decode($getData->detail_shipping, true);

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);

        $getUsersCartDetails = Product::selectRaw('users_cart_detail.id, product.name AS product_name,
            product.name, product.image, product.unit, product.price, users_cart_detail.qty')
            ->join('users_cart_detail', 'users_cart_detail.product_id', '=', 'product.id')
            ->where('users_cart_detail.users_cart_id', '=', $getUsersCart->id)
            ->where('choose', 1)->get();

        $getDetailsInformation = json_decode($getUsersCart->detail_information, true);

        $subTotal = 0;
        foreach ($getUsersCartDetails as $list) {
            $subTotal += ($list->qty * $list->price);
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'shipping' => $getDataShipping,
                'choose' => $getShipping ? $getShipping['shipping_id'] : 0,
                'cart_info' => [
                    'name' => $getDetailsInformation['receiver'] ?? '',
                    'address' => $getDetailsInformation['address'] ?? '',
                    'phone' => $getDetailsInformation['phone'] ?? '',
                    'subtotal' => $subTotal
                ],
            ]
        ]);

    }

    public function updateShipping()
    {

        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'shipping_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getShippingId = $this->request->get('shipping_id');
        $getShipping = Shipping::where('id', $getShippingId)->first();
        if (!$getShipping) {
            return response()->json([
                'success' => 0,
                'message' => ['Pengiriman Tidak Ditemukan'],
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
        $shippingId = $getDetailsShipping['shipping_id'] ?? 0;
        $getShipping = Shipping::where('id', $shippingId)->first();
        if (!$getShipping) {
            return response()->json([
                'success' => 0,
                'message' => ['Pengiriman Tidak Ditemukan'],
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
                'message' => ['Pengiriman Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getShippingPrice = 15000;

        $getPayment = Payment::where('status', 80)->orderBy('orders', 'ASC')->get();

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

        $needPhone = 0;
        $validator = Validator::make($this->request->all(), [
            'payment_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $paymentId = intval($this->request->get('payment_id'));
        $getPayment = Payment::where('id', $paymentId)->first();
        if (!$getPayment) {
            return response()->json([
                'success' => 0,
                'message' => ['Payment Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        if ($getPayment->type == 2 && $getPayment->service == 'xendit' && in_array($getPayment->type_payment, ['ew_ovo', 'ew_linkaja'])) {
            $needPhone = 1;
            $validator = Validator::make($this->request->all(), [
                'phone' => 'required|regex:/^(8\d+)/|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $validator->messages()->all(),
                    'token' => $this->request->attributes->get('_refresh_token'),
                ], 422);
            }
        }

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $user->id,
        ]);
        $getDetailsShipping = json_decode($getUsersCart->detail_shipping, true);
        $shippingId = $getDetailsShipping['shipping_id'];
        $getShipping = Shipping::where('id', $shippingId)->first();
        if (!$getShipping) {
            return response()->json([
                'success' => 0,
                'message' => ['Pengiriman Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getShippingPrice = 15000;

        $getUsersCartDetails = Product::selectRaw('product.price, users_cart_detail.qty')
            ->join('users_cart_detail', 'users_cart_detail.product_id', '=', 'product.id')
            ->where('users_cart_detail.users_cart_id', '=', $getUsersCart->id)
            ->where('choose', 1)->get();
        $total = 0;
        if ($getUsersCartDetails) {
            foreach ($getUsersCartDetails as $getUsersCartDetail) {
                $total += $getUsersCartDetail->price * $getUsersCartDetail->qty;
            }
        }

        if ($total <= 0) {
            return response()->json([
                'success' => 0,
                'message' => ['Tidak ada Produk yang di pilih'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $total += $getShippingPrice;

        $getTotal = Transaction::where('klinik_id', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT).rand(100,199);

        $sendData = [
            'job' => [
                'code' => $newCode,
                'payment_id' => $paymentId,
                'user_id' => $user->id,
                'type_service' => 'product',
                'service_id' => 0
            ],
            'code' => $newCode,
            'total' => $total,
            'name' => $user->fullname
        ];

        if ($needPhone == 1) {
            $sendData['phone'] = $this->request->get('phone');
        }

        $setLogic = new SynapsaLogic();
        $getPaymentInfo = $setLogic->createPayment($getPayment, $sendData);
        if ($getPaymentInfo['success'] == 1) {

            return response()->json([
                'success' => 1,
                'data' => [
                    'payment' => 0,
                    'info' => $getPaymentInfo['info']
                ],
                'message' => ['Berhasil'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        else {
            return response()->json([
                'success' => 0,
                'message' => [$getPaymentInfo['message'] ?? '-'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

    }

}
