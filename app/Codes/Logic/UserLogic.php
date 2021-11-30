<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\DeviceToken;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\LabCart;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\UsersCart;
use App\Codes\Models\V1\UsersCartDetail;
use Illuminate\Support\Facades\DB;

class UserLogic
{
    public function __construct()
    {
    }

    /**
     * @param $saveData
     * @return mixed
     */
    public function userCreatePatient($saveData)
    {
        $getUser = Users::create([
            'klinik_id' => intval($saveData['klinik_id']) ?? '',
            'province_id' => intval($saveData['province_id']) ?? '',
            'city_id' => intval($saveData['city_id']) ?? '',
            'district_id' => intval($saveData['district_id']) ?? '',
            'sub_district_id' => intval($saveData['sub_district_id']) ?? '',
            'fullname' => $saveData['fullname'] ?? '',
            'address' => $saveData['address'] ?? '',
            'address_detail' => $saveData['address_detail'] ?? '',
            'zip_code' => $saveData['zip_code'] ?? '',
            'dob' => $saveData['dob'] ?? '',
            'gender' => $saveData['gender'] ?? '',
            'nik' => $saveData['nik'] ?? '',
            'phone' => $saveData['phone'] ?? '',
            'email' => $saveData['email'] ?? '',
            'password' => bcrypt($saveData['password']),
            'status' => isset($saveData['status']) ? intval($saveData['status']) : 80,
            'patient' => 1,
            'upload_ktp' => $saveData['upload_ktp'],
            'image' => $saveData['image'],
        ]);

        UsersAddress::create([
            'user_id' => $getUser->id,
            'province_id' => intval($saveData['province_id']) ?? '',
            'city_id' => intval($saveData['city_id']) ?? '',
            'district_id' => intval($saveData['district_id']) ?? '',
            'sub_district_id' => intval($saveData['sub_district_id']) ?? '',
            'zip_code' => $saveData['zip_code'] ?? '',
            'address_name' => $saveData['fullname'] ?? '',
            'address' => $saveData['address'] ?? '',
            'address_detail' => $saveData['address_detail'] ?? ''
        ]);

        return $getUser;

    }

    /**
     * @param $userId
     * @param $saveData
     * @param null $user
     * @return false|mixed
     */
    public function userUpdatePatient($userId, $saveData, $user = null)
    {
        if ($user == null) {
            $getUser = Users::where('id', '=', $userId)->first();
        }
        else {
            $getUser = $user;
        }

        if (!$getUser) {
            return false;
        }

        foreach ($saveData as $key => $val) {
            if ($key == 'password') {
                $getUser->$key = bcrypt($val);
            }
            else {
                $getUser->$key = $val;
            }
        }

        $getUser->save();

        return $getUser;

    }

    /**
     * @param $userId
     * @param $saveData
     * @return false|mixed
     */
    public function userUpdateAddressPatient($userId, $saveData)
    {
        $getUsersAddress = UsersAddress::firstOrCreate([
            'user_id' => $userId
        ]);
        if (!$getUsersAddress) {
            return false;
        }

        $listUser = [
            'phone',
            'province_id',
            'city_id',
            'district_id',
            'sub_district_id',
            'address',
            'address_detail',
            'zip_code'
        ];
        $listAddress = [
            'province_id',
            'city_id',
            'district_id',
            'sub_district_id',
            'address_name',
            'address',
            'address_detail',
            'zip_code'
        ];

        DB::beginTransaction();

        $getUser = Users::where('id', '=', $userId)->first();
        if ($getUser) {
            foreach ($saveData as $key => $val) {
                if (in_array($key, $listUser)) {
                    $getUser->$key = $val;
                }
            }
            $getUser->save();
        }

        foreach ($saveData as $key => $val) {
            if (in_array($key, $listAddress)) {
                $getUsersAddress->$key = $val;
            }
        }

        $getUsersAddress->save();

        DB::commit();

        return $getUsersAddress;

    }

    /**
     * @param $userId
     * @param $getToken
     */
    public function updateToken($userId, $getToken)
    {
        $getDeviceToken = DeviceToken::firstOrCreate([
            'token' => $getToken
        ]);
        $getDeviceToken->getUser()->sync([$userId]);
    }

    /**
     * @param $userId
     * @param null $user
     * @return array|false
     */
    public function userInfo($userId, $user = null)
    {
        if ($user == null) {
            $getUser = Users::where('id', '=', $userId)->first();
        }
        else {
            $getUser = $user;
        }

        if (!$getUser) {
            return false;
        }

        $getKlinik = Klinik::where('id', '=', $getUser->klinik_id)->first();

        $result = [
            'user_id' => $getUser->id,
            'klinik_id' => $getUser->klinik_id,
            'klinik_name' => $getKlinik ? $getKlinik->name : '',
            'klinik_theme' => $getKlinik ? $getKlinik->theme_color : '',
            'klinik_logo' => $getKlinik ? $getKlinik->logo_full : '',
            'nik' => $getUser->nik,
            'fullname' => $getUser->fullname,
            'address' => $getUser->address,
            'address_detail' => $getUser->address_detail,
            'zip_code' => $getUser->zip_code,
            'gender' => intval($getUser->gender) == 1 ? 1 : 2,
            'phone' => $getUser->phone,
            'dob' => $getUser->dob,
            'email' => $getUser->email,
            'patient' => $getUser->patient,
            'doctor' => $getUser->doctor,
            'nurse' => $getUser->nurse,
            'status' => $getUser->status,
            'image' => $getUser->image_full,
            'ktp' => $getUser->upload_ktp_full,
            'status_nice' => $getUser->status_nice,
            'gender_nice' => $getUser->gender_nice,
            'join' => date('d F Y', strtotime($getUser->created_at))

        ];
        if ($getUser->doctor == 1) {
            $getDoctor = Doctor::selectRaw('formal_edu, nonformal_edu, doctor_category_id, doctor_category.name AS doctor_category')
                ->join('doctor_category', 'doctor_category.id', '=', 'doctor.doctor_category_id')
                ->where('user_id', '=', $getUser->id)->first();
            $result['info_doctor'] = $getDoctor;
        }

        return $result;
    }

    /**
     * @param $userId
     * @param null $phone
     * @return false|array
     */
    public function userAddress($userId, $phone = null)
    {
        $getUserAddress = UsersAddress::where('user_id', '=', $userId)->first();
        if (!$getUserAddress) {
            return false;
        }

        $getUserAddress = $getUserAddress->toArray();

        if ($phone == null) {
            $getUser = Users::where('id', $userId)->first();
            $getUserAddress['phone'] = $getUser->phone;
        }
        else {
            $getUserAddress['phone'] = $phone;
        }

        return $getUserAddress;
    }

    /**
     * @param $userId
     * @param int $choose
     * @return array
     */
    public function userCartProduct($userId, int $choose = 0): array
    {
        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $userId
        ]);

        $getDetailShipping = json_decode($getUsersCart->detail_shipping, true);
        $shippingId = $getDetailShipping['shipping_id'] ?? 0;

        $getUsersCartDetail = Product::selectRaw('users_cart_detail.id, product.id as product_id, product.name,
            product.image, product.price, product.unit, product.stock, product.stock_flag, product.type, product.status,
            users_cart_detail.qty, users_cart_detail.choose')
            ->join('users_cart_detail', 'users_cart_detail.product_id', '=', 'product.id')
            ->where('users_cart_detail.users_cart_id', '=', $getUsersCart->id);

        if ($choose == 1) {
            $getUsersCartDetail = $getUsersCartDetail->where('choose', '=', 1);
        }

        $getUsersCartDetail = $getUsersCartDetail->get();

        $totalQty = 0;
        $totalPrice = 0;
        foreach ($getUsersCartDetail as $list) {
            $totalQty += $list->qty;
            $totalPrice += ($list->qty * $list->price);
        }

        $result = [
            'shipping_id' => $shippingId,
            'cart_info' => $getUsersCart,
            'cart' => $getUsersCartDetail,
            'total_qty' => $totalQty,
            'total_qty_nice' => number_format_local($totalQty),
            'total_price' => $totalPrice,
            'total_price_nice' => number_format_local($totalPrice)
        ];

        if ($shippingId > 0) {
            $getShipping = Shipping::where('id', '=', $shippingId)->first();
            if ($getShipping) {
                $shippingPrice = $getShipping->price;

                $result['shipping_name'] = $getShipping->name;
                $result['shipping_price'] = $getShipping->price;
                $result['shipping_price_nice'] = $getShipping->shipping_price_nice;
                $result['subtotal'] = $totalPrice;
                $result['total'] = $totalPrice + $shippingPrice;

            }
        }

        return $result;

    }

    /**
     * Note:
     * 90 => Product Not Found
     * 91 => Product Stock not enough
     * 92 => Product Stock cannot be 0
     * @param $userId
     * @param $productId
     * @param int $qty
     * @return array
     */
    public function userCartProductAdd($userId, $productId, int $qty = 1): array
    {
        $getProduct = Product::where('id', '=', $productId)->where('status', '=', 80)->first();
        if(!$getProduct) {
            return [
                'success' => 90,
                'message' => 'Product Not Found'
            ];
        }

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $userId
        ]);

        $getUsersCartDetail = UsersCartDetail::where('users_cart_id', '=', $getUsersCart->id)->where('product_id', '=', $productId)->first();
        if ($getUsersCartDetail) {
            $getUsersCartDetail->qty += $qty;
            if ($getUsersCartDetail->qty <= 0) {
                return [
                    'success' => 92,
                    'message' => 'Product Stock cannot be 0'
                ];
            }
            else if($getProduct->stock_flag == 2 && $getProduct->stock < $getUsersCartDetail->qty) {
                return [
                    'success' => 91,
                    'message' => 'Product Stock not enough'
                ];
            }

            $getUsersCartDetail->save();

            $qty = $getUsersCartDetail->qty;

        }
        else {
            if ($qty <= 0) {
                return [
                    'success' => 92,
                    'message' => 'Product Stock cannot be 0'
                ];
            }
            else if($getProduct->stock_flag == 2 && $getProduct->stock < $qty) {
                return [
                    'success' => 91,
                    'message' => 'Product Stock not enough'
                ];
            }

            UsersCartDetail::firstOrCreate([
                'users_cart_id' => $getUsersCart->id,
                'product_id' => $productId
            ], [
                'qty' => $qty,
                'choose' => 0
            ]);

        }

        return [
            'success' => 80,
            'product_id' => $productId,
            'qty' => $qty,
            'qty_nice' => number_format_local($qty),
            'message' => 'Product Success'
        ];

    }

    /**
     * Note:
     * 90 => Product Not Found
     * 91 => Product Stock not enough
     * 92 => Product Stock cannot be 0
     * 93 => Product Cart Not Found
     * @param $userId
     * @param $usersCartDetailId
     * @param int $qty
     * @return array
     */
    public function userCartProductUpdateQty($userId, $usersCartDetailId, int $qty = 1): array
    {
        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $userId
        ]);

        $getUsersCartDetail = UsersCartDetail::where('users_cart_id', '=', $getUsersCart->id)
            ->where('id', '=', $usersCartDetailId)->first();
        if (!$getUsersCartDetail) {
            return [
                'success' => 93,
                'message' => 'Product Cart Not Found'
            ];
        }

        $getUsersCartDetail->qty = $qty;

        if ($getUsersCartDetail->qty <= 0) {
            return [
                'success' => 92,
                'message' => 'Product Stock cannot be 0'
            ];
        }

        $getProduct = Product::where('id', '=', $getUsersCartDetail->product_id)->where('status', '=', 80)->first();
        if(!$getProduct) {
            return [
                'success' => 90,
                'message' => 'Product Not Found'
            ];
        }
        else if($getProduct->stock_flag == 2 && $getProduct->stock < $getUsersCartDetail->qty) {
            return [
                'success' => 91,
                'message' => 'Product Stock not enough'
            ];
        }

        $getUsersCartDetail->save();

        $qty = $getUsersCartDetail->qty;

        return [
            'success' => 80,
            'product_id' => $getUsersCartDetail->product_id,
            'qty' => $qty,
            'qty_nice' => number_format_local($qty),
            'message' => 'Product Success'
        ];

    }

    /**
     * Note:
     * 93 => Product Cart Not Found
     * @param $userId
     * @param $usersCartDetailId
     * @return array
     */
    public function userCartProductDelete($userId, $usersCartDetailId): array
    {
        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $userId
        ]);

        $getUsersCartDetail = UsersCartDetail::where('users_cart_id', '=', $getUsersCart->id)
            ->where('id', '=', $usersCartDetailId)->first();
        if (!$getUsersCartDetail) {
            return [
                'success' => 93,
                'message' => 'Product Cart Not Found'
            ];
        }

        $getUsersCartDetail->delete();

        return [
            'success' => 80,
            'message' => 'Product Cart Success Remove'
        ];

    }

    /**
     * @param $userId
     * @param $productIds
     * @return int
     */
    public function userCartProductChoose($userId, $productIds): int
    {
        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $userId
        ]);

        $total = 0;
        DB::beginTransaction();

        UsersCartDetail::where('users_cart_id', '=', $getUsersCart->id)->whereNotIn('product_id', $productIds)->update([
            'choose' => 0
        ]);

        $getUsersCartDetail = UsersCartDetail::where('users_cart_id', '=', $getUsersCart->id)->whereIn('product_id', $productIds)->get();
        foreach ($getUsersCartDetail as $item) {
            $item->choose = 1;
            $item->save();
            $total++;
        }

        DB::commit();

        return $total;

    }

    /**
     * @param $userId
     * @param $shippingId
     * @return int
     */
    public function userCartProductUpdateShipping($userId, $shippingId): int
    {
        $getShipping = Shipping::where('id', $shippingId)->first();
        if (!$getShipping) {
            return 0;
        }

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $userId
        ]);

        $getDetailShipping = json_decode($getUsersCart->detail_shipping, true);
        if (is_array($getDetailShipping)) {
            $getDetailShipping['shipping_id'] = $getShipping->id;
            $getDetailShipping['shipping_name'] = $getShipping->name;
        }
        else {
            $getDetailShipping = [
                'shipping_id' => $getShipping->id,
                'shipping_name' => $getShipping->name
            ];
        }

        $getUsersCart->detail_shipping = json_encode($getDetailShipping);
        $getUsersCart->save();

        return 1;

    }

    /**
     * @param $userId
     * @param int $choose
     * @return array
     */
    public function userCartLab($userId, int $choose = 0): array
    {
        $getLabCart = LabCart::where('user_id', '=', $userId);
        if ($choose == 1) {
            $getLabCart = $getLabCart->where('choose', '=', 1);
        }
        $getLabCart = $getLabCart->get();

        $labIds = [];
        $labCartIds = [];
        $serviceId = 0;
        $getChoose = [];
        foreach ($getLabCart as $list) {
            $labIds[] = $list->lab_id;
            $labCartIds[$list->lab_id] = $list->id;
            $serviceId = $list->service_id;
            if ($list->choose == 1) {
                $getChoose[$list->lab_id] = 1;
            }
        }

        $labLogic = new LabLogic();
        $getService = $labLogic->getListService($serviceId);

        $total = 0;
        if (count($labIds) > 0) {
            $getData = Lab::selectRaw('lab.id AS lab_id, lab.parent_id, lab.name, lab.image, lab.desc_lab, lab.desc_benefit,
                lab.desc_preparation, lab.recommended_for, lab_service.service_id, lab_service.price')
                ->join('lab_service', 'lab_service.lab_id','=','lab.id')
                ->whereIn('lab.id', $labIds)
                ->where('lab_service.service_id','=', $serviceId)->get()->toArray();

            $temp = [];
            foreach ($getData as $list) {
                $total += $list['price'];
                $list['choose'] = isset($getChoose[$list['lab_id']]) ? intval($getChoose[$list['lab_id']]) : 0;
                $list['id'] = isset($labCartIds[$list['lab_id']]) ? intval($labCartIds[$list['lab_id']]) : 0;
                $temp[] = $list;
            }
            $getData = $temp;

        }
        else {
            $getData = [];
        }

        return [
            'cart' => $getData,
            'service' => $getService['data'],
            'sub_service' => $getService['sub_service'],
            'service_id' => $serviceId,
            'total' => $total,
            'total_nice' => number_format_local($total)
        ];

    }

    /**
     * @param $userId
     * @param $labId
     * @param $serviceId
     * @return int
     */
    public function userCartLabAdd($userId, $labId, $serviceId): int
    {
        $getLab = Lab::where('id', '=', $labId)->first();
        if (!$getLab) {
            return 92;
        }
        if ($getLab->parent_id > 0) {
            $haveParent = LabCart::where('user_id', '=', $userId)->where('lab_id', '=', $getLab->parent_id)->first();
            if (!$haveParent) {
                return 93;
            }
        }

        $getLabCart = LabCart::where('user_id', '=', $userId)->first();
        if ($getLabCart) {
            if ($getLabCart->service_id == $serviceId) {
                $insert = 80;
            }
            else {
                $insert = 91;
            }
        }
        else {
            $insert = 80;
        }

        if ($insert == 80) {
            LabCart::firstOrCreate([
                'user_id' => $userId,
                'lab_id' => $labId,
                'service_id' => $serviceId
            ]);
        }
        return $insert;
    }

    /**
     * @param $userId
     * @param $labCartId
     * @return int
     */
    public function userCartLabRemove($userId, $labCartId): int
    {
        $getLabCart = LabCart::where('user_id', '=', $userId)->where('id', '=', $labCartId)->first();
        if ($getLabCart) {
            DB::beginTransaction();
            $getLabChild = Lab::where('parent_id', $getLabCart->lab_id)->pluck('id')->toArray();
            if (count($getLabChild) > 0) {
                LabCart::where('user_id', '=', $userId)->whereIn('lab_id', $getLabChild)->delete();
            }
            $getLabCart->delete();
            DB::commit();
            return 1;
        }
        return 0;
    }

    /**
     * @param $userId
     * @param $labCartIds
     * @return int
     */
    public function userCartLabChoose($userId, $labCartIds): int
    {
        if (count($labCartIds) > 0) {
            DB::beginTransaction();
            LabCart::where('user_id', $userId)->whereNotIn('id', $labCartIds)->update([
                'choose' => 0
            ]);
            $getLabCarts = LabCart::where('user_id', $userId)->whereIn('id', $labCartIds)->get();
            if ($getLabCarts->count() > 0) {
                foreach ($getLabCarts as $getLabCart) {
                    $getLabCart->choose = 1;
                    $getLabCart->save();
                }
                DB::commit();
                return 1;
            }
            DB::commit();
        }
        return 0;
    }

}
