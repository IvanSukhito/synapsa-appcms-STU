<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\DeviceToken;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Product;
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
            'status' => intval($saveData['status']) ?? 80,
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

        $getUser = Users::where('id', '=', $userId)->first();
        if ($getUser) {
            foreach ($saveData as $key => $val) {
                $getUser->$key = $val;
            }
            $getUser->save();
        }

        foreach ($saveData as $key => $val) {
            $getUsersAddress->$key = $val;
        }

        $getUsersAddress->save();

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
            'fullname' => $getUser->fullname,
            'address' => $getUser->address,
            'address_detail' => $getUser->address_detail,
            'zip_code' => $getUser->zip_code,
            'gender' => intval($getUser->gender) == 1 ? 1 : 2,
            'phone' => $getUser->phone,
            'email' => $getUser->email,
            'patient' => $getUser->patient,
            'doctor' => $getUser->doctor,
            'nurse' => $getUser->nurse,
            'status' => $getUser->status,
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
     * @return false|mixed
     */
    public function userAddress($userId)
    {
        $getUserAddress = UsersAddress::where('user_id', '=', $userId)->first();
        if (!$getUserAddress) {
            return false;
        }
        return $getUserAddress;
    }

    /**
     * @param $userId
     * @return array
     */
    public function userCart($userId)
    {
        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $userId
        ]);

        $getUsersCartDetail = Product::selectRaw('users_cart_detail.id, product.id as product_id, product.name, 
            product.image, product.price, product.unit, product.stock, product.stock_flag, product.type, product.status, 
            users_cart_detail.qty, users_cart_detail.choose')
            ->join('users_cart_detail', 'users_cart_detail.product_id', '=', 'product.id')
            ->where('users_cart_detail.users_cart_id', '=', $getUsersCart->id)->get();

        $totalQty = 0;
        $totalPrice = 0;
        foreach ($getUsersCartDetail as $list) {
            $totalQty += $list->qty;
            $totalPrice += ($list->qty * $list->price);
        }

        return [
            'cart_info' => $getUsersCart,
            'cart' => $getUsersCartDetail,
            'total_qty' => $totalQty,
            'total_qty_nice' => number_format_local($totalQty),
            'total_price' => $totalPrice,
            'total_price_nice' => number_format_local($totalPrice)
        ];

    }

    /**
     * @param $userId
     * @param $productId
     * @param int $qty
     * @return int
     */
    public function userCartAdd($userId, $productId, int $qty = 1): int
    {
        $getProduct = Product::where('id', '=', $productId)->where('status', '=', 80)->first();
        if(!$getProduct) {
            return 0;
        }
        elseif($getProduct->stock_flag == 2 && $getProduct->stock < $qty) {
            return 0;
        }

        $getUsersCart = UsersCart::firstOrCreate([
            'users_id' => $userId
        ]);

        $getUsersCartDetail = UsersCartDetail::firstOrCreate([
            'users_cart_id' => $getUsersCart->id,
            'product_id' => $productId
        ]);

        $getUsersCartDetail->qty = $qty;
        $getUsersCartDetail->save();

        return 1;

    }

    public function userCartUpdateQty($usersCartDetailId, $products)
    {
        $getProductIds = [];
        foreach ($products as $product => $qty) {
            $getProductIds[] = $product;
        }

        DB::beginTransaction();
        $getUsersCartDetail = UsersCartDetail::where('users_cart_id', '=', $usersCartDetailId)
            ->whereIn('product_id', $getProductIds)->get();
        foreach ($getUsersCartDetail as $item) {
            $item->qty = intval($products[$item->product_id]) ?? 1;
            $item->save();
        }
        DB::commit();

        return true;
    }

    public function userCartChoose($usersCartDetailId, $productIds)
    {
        DB::beginTransaction();
        UsersCartDetail::where('users_cart_id', '=', $usersCartDetailId)->whereIn('product_id', $productIds)->update([
            'choose' => 1
        ]);
        UsersCartDetail::where('users_cart_id', '=', $usersCartDetailId)->whereNotIn('product_id', $productIds)->update([
            'choose' => 0
        ]);
        DB::commit();

        return true;
    }

}
