<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\ProductLogic;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Logic\UserLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\ProductCategory;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\UsersCartDetail;
use App\Codes\Models\V1\UsersCart;
use App\Codes\Models\V1\Transaction;
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
        $this->setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
        $this->limit = 10;
    }

    public function getProduct()
    {
        $user = $this->request->attributes->get('_user');

        $s = strip_tags($this->request->get('s'));
        $categoryId = intval($this->request->get('category_id'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $productLogic = new ProductLogic();

        return response()->json([
            'success' => 1,
            'data' => $productLogic->productGet($user->klinik_id, $getLimit, $categoryId, $s),
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    /**
     * @deprecated
     */
    public function getProductRujukan()
    {
        $user = $this->request->attributes->get('_user');

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $data = Product::selectRaw('id, name, image, unit, price, stock, stock_flag');
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

    /**
     * @deprecated
     */
    public function getProductPriority()
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
        $data = $data->where('status', 80)->where('top', 1)->where('klinik_id', $user->klinik_id)->orderBy('id','DESC')->paginate($getLimit);
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

        $productLogic = new ProductLogic();
        $getData = $productLogic->productInfo($user->klinik_id, $id);
        if (!$getData) {
            return response()->json([
                'success' => 0,
                'message' => ['Produk Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else {
            return response()->json([
                'success' => 1,
                'data' => $getData,
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

    }

    public function getCart()
    {
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();

        return response()->json([
            'success' => 1,
            'data' => $userLogic->userCart($user->id),
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
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $productId = $this->request->get('product_id');
        $qty = $this->request->get('qty');

        $userLogic = new UserLogic();
        $getResult = $userLogic->userCartAdd($user->id, $productId, $qty);
        if($getResult['success'] == 90){
            return response()->json([
                'success' => 0,
                'message' => ['Produk Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else if ($getResult['success'] == 91) {
            return response()->json([
                'success' => 0,
                'message' => ['Stock Produk tidak mencukupi'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'message' => ['Data Berhasil Dimasukan'],
            'data' => [
                'product_id' => $productId,
                'qty' => $getResult['qty'],
                'qty_nice' => $getResult['qty_nice'],
            ],
            'token' => $this->request->attributes->get('_refresh_token')
        ]);
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

        $userLogic = new UserLogic();
        $getResult = $userLogic->userCartUpdateQty($user->id, $id, $getQty);
        if($getResult['success'] == 90){
            return response()->json([
                'success' => 0,
                'message' => ['Produk Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else if ($getResult['success'] == 91) {
            return response()->json([
                'success' => 0,
                'message' => ['Stock Produk tidak mencukupi'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else if ($getResult['success'] == 92) {
            return response()->json([
                'success' => 0,
                'message' => ['Stock Produk tidak boleh kosong'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else if ($getResult['success'] == 93) {
            return response()->json([
                'success' => 0,
                'message' => ['Cart Produk tidak ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'message' => ['Data Berhasil Dimasukan'],
            'data' => [
                'product_id' => $getResult['product_id'],
                'qty' => $getResult['qty'],
                'qty_nice' => $getResult['qty_nice'],
            ],
            'token' => $this->request->attributes->get('_refresh_token')
        ]);

    }

    public function deleteCart($id)
    {
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();
        $getResult = $userLogic->userCartDelete($user->id, $id);
        if ($getResult['success'] == 93) {
            return response()->json([
                'success' => 0,
                'message' => ['Cart Produk tidak ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'message' => ['Produk berhasil dihapus'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
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

        $getListProductIds = $this->request->get('product_ids');
        if (!is_array($getListProductIds)) {
            $getListProductIds = [$getListProductIds];
        }

        $userLogic = new UserLogic();
        $haveProduct = $userLogic->userCartChoose($user->id, $getListProductIds);

        if ($haveProduct > 0) {
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
            ], 422);
        }
    }

    public function getReceiver()
    {
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();

        return response()->json([
            'success' => 1,
            'data' => $userLogic->userAddress($user->id)
        ]);
    }

    public function updateReceiver()
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'receiver' => 'required',
            'address' => 'required',
            'phone' => 'required|regex:/^(8\d+)/|numeric|unique:users,phone,'.$user->id,
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $saveData = [
            'receiver' => strip_tags($this->request->get('receiver')),
            'address_name' => strip_tags($this->request->get('receiver')),
            'address' => strip_tags($this->request->get('address')),
            'phone' => strip_tags($this->request->get('phone')),
        ];

        $listValidate = [
            'phone',
            'province_id',
            'city_id',
            'district_id',
            'sub_district_id',
            'address_detail',
            'zip_code'
        ];

        foreach ($listValidate as $key) {
            if ($this->request->get($key)) {
                $saveData[$key] = $this->request->get($key);
            }
        }

        $userLogic = new UserLogic();
        $userLogic->userUpdateAddressPatient($user->id, $saveData);

        return response()->json([
            'success' => 1,
            'message' => ['Detail Informasi Berhasil Diperbarui'],
            'data' => $saveData
        ]);
    }

    public function getAddress()
    {
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();

        return response()->json([
            'success' => 1,
            'data' => $userLogic->userAddress($user->id)
        ]);
    }

    public function getShipping()
    {
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();
        $getUserAddress = $userLogic->userAddress($user->id);

        $getUserCart = $userLogic->userCart($user->id);
        $subTotal = $getUserCart['total_price'];

        if ($getUserAddress) {
            $getUserAddress = $getUserAddress->toArray();
            $getUserAddress['name'] = $getUserAddress['receiver'];
            $getUserAddress['subtotal'] = $subTotal;
            $getUserAddress['subtotal_nice'] = number_format_local($subTotal);
        }

        $getDataShipping = Shipping::where('status', 80)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'shipping' => $getDataShipping,
                'choose' => $getUserCart['shipping_id'],
                'cart_info' => $getUserAddress,
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

        $getShippingPrice = $getShipping->price;

        return response()->json([
            'success' => 1,
            'data' => [
                'cart_info' => [
                    'name' => $getDetailsInformation['receiver'] ?? '',
                    'address' => $getDetailsInformation['address'] ?? '',
                    'phone' => $getDetailsInformation['phone'] ?? '',
                    'shipping_name' => $getShipping->name,
                    'shipping_price' => $getShippingPrice,
                    'shipping_price_nice' => $getShipping->shipping_price_nice,
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
        $shippingId = $getDetailsShipping['shipping_id'] ?? 0;
        $getShipping = Shipping::where('id', $shippingId)->first();
        if (!$getShipping) {
            return response()->json([
                'success' => 0,
                'message' => ['Pengiriman Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getShippingPrice = $getShipping->price;

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
                    'shipping_price_nice' => $getShipping->shipping_price_nice,
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

        if ($getPayment->type == 2 && $getPayment->service == 'xendit' && in_array($getPayment->type_payment, ['ew_ovo', 'ew_dana', 'ew_linkaja'])) {
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

        $getShippingPrice = $getShipping->price;

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

        //dd($getTotal);
        $newCode = str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT).rand(100,199);
        //dd($newCode);
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
            ], 422);
        }

    }

}
