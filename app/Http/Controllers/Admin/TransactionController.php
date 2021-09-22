<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\ProductCategory;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TransactionController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'klinik_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.klinik',
                'type' => 'select2',
            ],
            'user_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.name',
                'type' => 'select2',
            ],
            'payment_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.payment_id',
                'type' => 'select2',
            ],
            'shipping_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.shipping',
                'type' => 'select2',
            ],
            'code' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.code',
            ],
            'payment_name' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'lang' => 'general.payment_name',
            ],
            'payment_detail' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'lang' => 'general.payment_detail',
            ],
            'shipping_name' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'lang' => 'general.shipping_name',
            ],
            'shipping_address_name' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'lang' => 'general.shipping_address_name',
            ],
            'shipping_address' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'lang' => 'general.shipping_address',
            ],
            'shipping_city_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'type' => 'select2',
                'lang' => 'general.shipping_city_id',
            ],
            'shipping_district_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'type' => 'select2',
                'lang' => 'general.shipping_district_id',
            ],
            'shipping_subdistrict_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'type' => 'select2',
                'lang' => 'general.shipping_subdistrict_id',
            ],
            'shipping_zipcode' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'lang' => 'general.shipping_zipcode',
            ],
            'shipping_price' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'type' => 'number',
                'lang' => 'general.shipping_price',
            ],
            'total_qty' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'type' => 'number',
                'lang' => 'general.total_qty',
            ],
            'subtotal' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'type' => 'number',
                'lang' => 'general.subtotal',
            ],
            'total' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'number',
                'lang' => 'general.total',
            ],
            'receiver_name' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.receiver_name',
            ],
            'receiver_phone' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'lang' => 'general.receiver_phone',
            ],
            'receiver_address' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'lang' => 'general.receiver_address',
            ],
            'type' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'type' => 'select',
                'lang' => 'general.type',
            ],
            'extra_info' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
                'lang' => 'general.extra_info',
            ],
            'status' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
                'lang' => 'general.status',
            ],
            'created_at' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'datetime',
                'lang' => 'general.created_at',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'custom' => ',orderable:false'
            ]
        ];

        parent::__construct(
            $request, 'general.transaction', 'transaction', 'V1\Transaction', 'transaction',
            $passingData
        );


        $getUsers = Users::where('status', 80)->where('patient',1)->pluck('fullname', 'id')->toArray();
        if($getUsers) {
            foreach($getUsers as $key => $value) {
                $listUsers[$key] = $value;
            }
        }

        $getKlinik = Klinik::where('status', 80)->pluck('name', 'id')->toArray();
        if($getKlinik) {
            foreach($getKlinik as $key => $value) {
                $listKlinik[$key] = $value;
            }
        }

        $getPayment = Payment::where('status', 80)->pluck('name', 'id')->toArray();
        if($getPayment) {
            foreach($getPayment as $key => $value) {
                $listPayment[$key] = $value;
            }
        }
        $getShipping = Shipping::where('status', 80)->pluck('name', 'id')->toArray();
        if($getShipping) {
            foreach($getShipping as $key => $value) {
                $listShipping[$key] = $value;
            }
        }

        $getShippingCity = City::pluck('name', 'id')->toArray();
        if($getShippingCity) {
            foreach($getShippingCity as $key => $value) {
                $listShippingCity[$key] = $value;
            }
        }

        $getShippingDistrict = District::pluck('name', 'id')->toArray();
        if($getShippingDistrict) {
            foreach($getShippingDistrict as $key => $value) {
                $listShippingDistrict[$key] = $value;
            }
        }

        $getShippingSubdistrict = SubDistrict::pluck('name', 'id')->toArray();
        if($getShippingSubdistrict) {
            foreach($getShippingSubdistrict as $key => $value) {
                $listShippingSubdistrict[$key] = $value;
            }
        }

        $this->data['listSet']['user_id'] = $listUsers;
        $this->data['listSet']['klinik_id'] = $listKlinik;
        $this->data['listSet']['payment_id'] = $listPayment;
        $this->data['listSet']['shipping_id'] = $listShipping;
        $this->data['listSet']['shipping_city_id'] = $listShippingCity;
        $this->data['listSet']['shipping_district_id'] = $listShippingDistrict;
        $this->data['listSet']['shipping_subdistrict_id'] = $listShippingSubdistrict;
        $this->data['listSet']['status'] = get_list_order_status();
        $this->data['listSet']['type'] = get_list_type_transaction();

    }

    public function store()
    {
        $this->callPermission();

        $viewType = 'create';

        $getListCollectData = collectPassingData($this->passingData, $viewType);
        $validate = $this->setValidateData($getListCollectData, $viewType);
        if (count($validate) > 0)
        {
            $data = $this->validate($this->request, $validate);
        }
        else {
            $data = [];
            foreach ($getListCollectData as $key => $val) {
                $data[$key] = $this->request->get($key);
            }
        }

        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $getShippingCity = $this->request->get('shipping_city_id');
        $getShippingName = City::where('id', $getShippingCity)->first();

        $getShippingDistrict = $this->request->get('shipping_district_id');
        $getShippingDistrictName = District::where('id', $getShippingDistrict)->first();

        $getShippingSubdistrict = $this->request->get('shipping_subdistrict_id');
        $getShippingSubdistrictName = SubDistrict::where('id', $getShippingSubdistrict)->first();

        $data['shipping_city_name'] = $getShippingName ? $getShippingName->name : '';
        $data['shipping_district_name'] = $getShippingDistrictName ? $getShippingDistrictName->name : '';
        $data['shipping_subdistrict_name'] = $getShippingSubdistrictName ? $getShippingSubdistrictName->name : '';

        $getData = $this->crud->store($data);

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.show', $id);
        }
    }
}
