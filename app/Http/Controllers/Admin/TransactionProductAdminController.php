<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\Admin;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\ProductCategory;
use App\Codes\Models\V1\Province;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class TransactionProductAdminController extends _CrudController
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
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'lang' => 'general.klinik',
                'type' => 'select2',
                'custom' => ', name: "klinik.name"'
            ],
            'user_id' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'lang' => 'general.name',
                'type' => 'select2',
                'custom' => ', name: "users.fullname"'
            ],
            'payment_id' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'list' => 0,
                'lang' => 'general.payment_id',
                'type' => 'select2',
            ],
            'shipping_id' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'list' => 0,
                'lang' => 'general.shipping',
                'type' => 'select2',
            ],
            'code' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'lang' => 'general.transaction_code',
            ],
            'payment_name' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'lang' => 'general.payment_name',
            ],
            'payment_detail' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'list' => 0,
                'lang' => 'general.payment_detail',
            ],
            'shipping_name' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'lang' => 'general.shipping_name',
            ],
            'shipping_address_name' => [
                'validate' => [
                    'create' => 'required',
                ],
                'edit' => 0,
                'list' => 0,
                'lang' => 'general.shipping_address_name',
            ],
            'shipping_address' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'list' => 0,
                'lang' => 'general.shipping_address',
            ],
            'shipping_province' => [
                    'list' => 0,
                    'extra' => [
                        'edit' => ['disabled' => true]
                    ],
            ],
            'shipping_city' => [
                'list' => 0,
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
            ],
            'shipping_district' => [
                'list' => 0,
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
            ],
            'shipping_subdistrict' => [
                'list' => 0,
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
            ],
            'shipping_zipcode' => [
                'validate' => [
                    'create' => 'required',
                ],
                'edit' => 0,
                'list' => 0,
                'lang' => 'general.shipping_zipcode',
            ],
            'shipping_price' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'list' => 0,
                'type' => 'money',
                'lang' => 'general.shipping_price',
            ],
            'total_qty' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'list' => 0,
                'type' => 'number',
                'lang' => 'general.total_qty',
            ],
            'subtotal' => [
                'validate' => [
                    'create' => 'required',
                ],
                'edit' => 0,
                'list' => 0,
                'type' => 'money',
                'lang' => 'general.subtotal',
            ],
            'total' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'type' => 'money',
                'list' => 0,
                'lang' => 'general.total',
            ],
            'receiver_name' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'lang' => 'general.receiver_name',
                'list' => 0,
            ],
            'receiver_phone' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'list' => 0,
                'lang' => 'general.receiver_phone',
            ],
            'receiver_address' => [
                'validate' => [
                    'create' => 'required',
                ],
                'extra' => [
                    'edit' => ['disabled' => true]
                ],
                'list' => 0,
                'lang' => 'general.receiver_address',
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
                ],
                'edit' => 0,
                'type' => 'datetime',
                'lang' => 'general.created_at',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show'  => 0,
                'custom' => ',orderable:false'
            ]
        ];

        parent::__construct(
            $request, 'general.transaction_product_admin', 'transaction-product-admin', 'V1\Transaction', 'transaction-product-admin',
            $passingData
        );
        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.transaction-product.list';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.transaction-product.forms';
        $this->listView['dataTable'] = env('ADMIN_TEMPLATE').'.page.transaction-product.list_button';

        $listUsers = [];
        $getUsers = Users::where('status', 80)->pluck('fullname', 'id')->toArray();
        if($getUsers) {
            foreach($getUsers as $key => $value) {
                $listUsers[$key] = $value;
            }
        }

        $listKlinik = [];
        $getKlinik = Klinik::where('status', 80)->pluck('name', 'id')->toArray();
        if($getKlinik) {
            foreach($getKlinik as $key => $value) {
                $listKlinik[$key] = $value;
            }
        }

        $listPayment = [];
        $getPayment = Payment::where('status', 80)->pluck('name', 'id')->toArray();
        if($getPayment) {
            foreach($getPayment as $key => $value) {
                $listPayment[$key] = $value;
            }
        }

        $listShipping = [];
        $getShipping = Shipping::where('status', 80)->pluck('name', 'id')->toArray();
        if($getShipping) {
            foreach($getShipping as $key => $value) {
                $listShipping[$key] = $value;
            }
        }

        $klinik_id = [0 => 'All'];
        foreach(Klinik::where('status', 80)->pluck('name', 'id')->toArray() as $key => $val) {
            $klinik_id[$key] = $val;
        }

        $payment_id = [0 => 'All'];
        foreach(Payment::where('status', 80)->pluck('name', 'id')->toArray() as $key => $val) {
            $payment_id[$key] = $val;
        }

        $shipping_id = [0 => 'All'];
        foreach(Shipping::where('status', 80)->pluck('name', 'id')->toArray() as $key => $val) {
            $shipping_id[$key] = $val;
        }

        $status = [];
        foreach(get_list_transaction() as $key => $val) {
            $status[$key] = $val;
        }

        $status_form = [0 => 'All'];
        foreach(get_list_transaction() as $key => $val) {
            $status_form[$key] = $val;
        }


        $this->data['listSet']['user_id'] = $listUsers;
        $this->data['listSet']['klinik_id'] = $listKlinik;
        $this->data['listSet']['filter_klinik_id'] = $klinik_id;
        $this->data['listSet']['status'] = $status;
        $this->data['listSet']['status_form'] = $status_form;
        $this->data['listSet']['filter_payment_id'] = $payment_id;
        $this->data['listSet']['filter_shipping_id'] = $shipping_id;
        $this->data['listSet']['payment_id'] = $listPayment;
        $this->data['listSet']['shipping_id'] = $listShipping;


    }
    public function show($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getTransaction = Transaction::selectRaw('transaction.*, province.name as shipping_province, city.name as shipping_city, district.name as shipping_district, sub_district.name as shipping_subdistrict')
            ->join('province','province.id','=','transaction.shipping_province_id','LEFT')
            ->join('city','city.id','=','transaction.shipping_city_id','LEFT')
            ->join('district','district.id','=','transaction.shipping_district_id','LEFT')
            ->join('sub_district','sub_district.id','=','transaction.shipping_subdistrict_id','LEFT')
            ->where('type_service', 1)
            ->where('transaction.id', $getData->id)
            ->first();

        $data = $this->data;

        $getTransactionDetails =   TransactionDetails::selectRaw('transaction_details.*, code, klinik.name as klinik')
            ->join('transaction','transaction.id','=','transaction_details.transaction_id','left')
            ->join('klinik','klinik.id','=','transaction.klinik_id','left')
            ->where('transaction_details.transaction_id', $getData->id)
            ->get();

        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getTransaction;
        $data['transaction'] = $getTransactionDetails;

        return view($this->listView[$data['viewType']], $data);
    }
    public function dataTable()
    {
        $this->callPermission();

        $dataTables = new DataTables();

        $builder = $this->model::query()->selectRaw('transaction.*, users.fullname AS user_id, klinik.name AS klinik_id')
            ->join('users', 'users.id', '=', 'transaction.user_id', 'LEFT')
            ->join('klinik', 'klinik.id', '=', 'transaction.klinik_id', 'LEFT')
            ->where('type_service', 1);

        if ($this->request->get('filter_payment_id') && $this->request->get('filter_payment_id') != 0) {
            $builder = $builder->where('payment_id', $this->request->get('filter_payment_id'));
        }
        if ($this->request->get('filter_klinik_id') && $this->request->get('filter_klinik_id') != 0) {
            $builder = $builder->where('klinik_id', $this->request->get('filter_klinik_id'));
        }
        if ($this->request->get('filter_shipping_id') && $this->request->get('filter_shipping_id') != 0) {
            $builder = $builder->where('shipping_id', $this->request->get('filter_shipping_id'));
        }
        if ($this->request->get('status') && $this->request->get('status') != 0) {
            $builder = $builder->where('status', $this->request->get('status'));
        }
        //if ()
        if ($this->request->get('daterange')) {
            $getDateRange = $this->request->get('daterange');
            $dateSplit = explode(' | ', $getDateRange);
            $dateStart = date('Y-m-d 00:00:00', strtotime($dateSplit[0]));
            $dateEnd = isset($dateSplit[1]) ? date('Y-m-d 23:59:59', strtotime($dateSplit[1])) : date('Y-m-d 23:59:59', strtotime($dateSplit[0]));

            $builder = $builder->whereBetween('created_at', [$dateStart, $dateEnd]);
        }

        $dataTables = $dataTables->eloquent($builder)
            ->addColumn('action', function ($query) {
                return view($this->listView['dataTable'], [
                    'query' => $query,
                    'thisRoute' => $this->route,
                    'permission' => $this->permission,
                    'masterId' => $this->masterId
                ]);
            });

        $listRaw = [];
        $listRaw[] = 'action';
        foreach (collectPassingData($this->passingData) as $fieldName => $list) {
            if (in_array($list['type'], ['select', 'select2'])) {
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName) {
                    $getList = isset($this->data['listSet'][$fieldName]) ? $this->data['listSet'][$fieldName] : [];
                    return isset($getList[$query->$fieldName]) ? $getList[$query->$fieldName] : $query->$fieldName;
                });
            }
            else if (in_array($list['type'], ['image', 'image_preview'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<img src="' . asset($list['path'] . $query->$fieldName) . '" class="img-responsive max-image-preview"/>';
                });
            }
            else if (in_array($list['type'], ['code'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<pre>' . json_encode(json_decode($query->$fieldName, true), JSON_PRETTY_PRINT) . '</pre>';
                });
            }
            else if (in_array($list['type'], ['money'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return number_format($query->$fieldName, 2);
                });
            }
            else if (in_array($list['type'], ['texteditor'])) {
                $listRaw[] = $fieldName;
            }
        }

        return $dataTables
            ->rawColumns($listRaw)
            ->make(true);
    }

    public function index()
    {
        $this->callPermission();

        $data = $this->data;

        $data['passing'] = collectPassingData($this->passingData);
        $data['type'] = 'admin';

        return view($this->listView['index'], $data);
    }
}
