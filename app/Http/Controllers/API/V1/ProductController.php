<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\ProductLogic;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Logic\UserLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\Transaction;
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
            'data' => $userLogic->userCartProduct($user->id),
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
        $getResult = $userLogic->userCartProductAdd($user->id, $productId, $qty);
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

    public function updateCart($id)
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
        $getResult = $userLogic->userCartProductUpdateQty($user->id, $id, $getQty);
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
        $getResult = $userLogic->userCartProductDelete($user->id, $id);
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
        $haveProduct = $userLogic->userCartProductChoose($user->id, $getListProductIds);

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
            'data' => $userLogic->userAddress($user->id, $user->phone)
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
            'data' => $userLogic->userAddress($user->id, $user->phone)
        ]);
    }

    public function getShipping()
    {
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();
        $getUserAddress = $userLogic->userAddress($user->id, $user->phone);

        $getUserCart = $userLogic->userCartProduct($user->id, 1);
        $subTotal = $getUserCart['total_price'];

        if ($getUserAddress) {
            $getUserAddress['name'] = $getUserAddress['receiver'];
            $getUserAddress['subtotal'] = $subTotal;
            $getUserAddress['subtotal_nice'] = number_format_local($subTotal);
        }

        $getDataShipping = Shipping::where('status', 80)->orderBy('orders', 'ASC')->get();

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

        $userLogic = new UserLogic();
        $getUserAddress = $userLogic->userCartProductUpdateShipping($user->id, $getShippingId);
        if ($getUserAddress == 0) {
            return response()->json([
                'success' => 0,
                'message' => ['Pengiriman Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        return response()->json([
            'success' => 1,
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function cartSummary()
    {
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();
        $getUserCart = $userLogic->userCartProduct($user->id, 1);
        $getUserAddress = $userLogic->userAddress($user->id, $user->phone);

        return response()->json([
            'success' => 1,
            'data' => [
                'cart_info' => [
                    'name' => $getUserAddress['receiver'] ?? '',
                    'address' => $getUserAddress['address'] ?? '',
                    'phone' => $getUserAddress['phone'] ?? '',
                    'shipping_name' => $getUserCart['shipping_name'] ?? '',
                    'shipping_price' => $getUserCart['shipping_price'] ?? '',
                    'shipping_price_nice' => $getUserCart['shipping_price_nice'] ?? '',
                    'subtotal' => $getUserCart['subtotal'] ?? 0,
                    'total' => $getUserCart['total'] ?? 0
                ],
                'cart_details' => $getUserCart['cart']
            ],
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getPayment()
    {
        $user = $this->request->attributes->get('_user');

        $userLogic = new UserLogic();
        $getUserCart = $userLogic->userCartProduct($user->id, 1);
        $getUserAddress = $userLogic->userAddress($user->id, $user->phone);

        $getPayment = Payment::where('status', '=', 80)->orderBy('orders', 'ASC')->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'cart_info' => [
                    'name' => $getUserAddress['receiver'] ?? '',
                    'address' => $getUserAddress['address'] ?? '',
                    'phone' => $getUserAddress['phone'] ?? '',
                    'shipping_name' => $getUserCart['shipping_name'] ?? '',
                    'shipping_price' => $getUserCart['shipping_price'] ?? '',
                    'shipping_price_nice' => $getUserCart['shipping_price_nice'] ?? '',
                    'subtotal' => $getUserCart['subtotal'] ?? 0,
                    'total' => $getUserCart['total'] ?? 0
                ],
                'cart_details' => $getUserCart['cart'],
                'payment' => $getPayment
            ],
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function checkout()
    {
        $user = $this->request->attributes->get('_user');

        $synapsaLogic = new SynapsaLogic();

        $paymentId = intval($this->request->get('payment_id'));
        $getPaymentResult = $synapsaLogic->checkPayment($paymentId);
        if ($getPaymentResult['success'] == 0) {
            return response()->json([
                'success' => 0,
                'message' => ['Payment Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $needPhone = intval($getPaymentResult['phone']);

        if ($needPhone == 1) {
            $validationRule = ['payment_id' => 'required|numeric', 'phone' => 'required|regex:/^(8\d+)/|numeric'];
        }
        else {
            $validationRule = ['payment_id' => 'required|numeric'];
        }

        $validator = Validator::make($this->request->all(), $validationRule);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getPhone = $this->request->get('phone');
        $getPayment = $getPaymentResult['payment'];

        $userLogic = new UserLogic();
        $getResult = $userLogic->userCartProduct($user->id, 1);
        $total = $getResult['total'] ?? 0;
        $shippingId = intval($getResult['shipping_id']) ?? 0;
        if ($total <= 0) {
            return response()->json([
                'success' => 0,
                'message' => ['Tidak ada Produk yang di pilih'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getTotal = Transaction::where('klinik_id', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT).rand(100000,999999);
        $sendData = [
            'job' => [
                'code' => $newCode,
                'payment_id' => $paymentId,
                'user_id' => $user->id,
                'shipping_id' => $shippingId,
                'type_service' => 'product',
                'service_id' => 0
            ],
            'code' => $newCode,
            'total' => $total,
            'name' => $user->fullname
        ];

        if ($needPhone == 1) {
            $sendData['phone'] = $getPhone;
        }

        $getPaymentInfo = $synapsaLogic->createPayment($getPayment, $sendData);
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
