<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Admin;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\ProductCategory;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class TransactionLabAdminController extends _CrudController
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
            'total' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'money',
                'lang' => 'general.total',
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
            $request, 'general.transaction_lab_admin', 'transaction-lab-admin', 'V1\Transaction', 'transaction-lab-admin',
            $passingData
        );
        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.transaction-lab.list';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.transaction-lab.forms';

        $this->listView['dataTable'] = env('ADMIN_TEMPLATE').'.page.transaction-lab.list_button';

        $listUsers = array_merge([0 => '-'], Users::where('status', 80)->pluck('fullname', 'id')->toArray());
        $listKlinik = array_merge([0 => '-'], Klinik::where('status', 80)->pluck('name', 'id')->toArray());
        $listPayment = array_merge([0 => '-'], Payment::where('status', 80)->pluck('name', 'id')->toArray());
        $klinik_id = array_merge([0 => 'All'], Klinik::where('status', 80)->pluck('name', 'id')->toArray());
        $payment_id = array_merge([0 => 'All'], Payment::where('status', 80)->pluck('name', 'id')->toArray());
        $status = array_merge([0 => '-'], get_list_transaction());
        $filter_status = array_merge([0 => 'All'], get_list_transaction());

        $this->data['listSet']['user_id'] = $listUsers;
        $this->data['listSet']['klinik_id'] = $listKlinik;
        $this->data['listSet']['filter_klinik_id'] = $klinik_id;
        $this->data['listSet']['filter_payment_id'] = $payment_id;
        $this->data['listSet']['payment_id'] = $listPayment;
        $this->data['listSet']['status'] = $status;
        $this->data['listSet']['filter_status'] = $filter_status;
        $this->data['listSet']['type'] = get_list_type_transaction();

    }
    public function dataTable()
    {
        $this->callPermission();

        $dataTables = new DataTables();

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();

        $builder = $this->model::query()->select('*')->where('type_service', 3);

        if ($this->request->get('filter_payment_id') && $this->request->get('filter_payment_id') != 0) {
            $builder = $builder->where('payment_id', $this->request->get('filter_payment_id'));
        }
        if ($this->request->get('filter_klinik_id') && $this->request->get('filter_klinik_id') != 0) {
            $builder = $builder->where('klinik_id', $this->request->get('filter_klinik_id'));
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
    public function show($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $getTransaction =   TransactionDetails::selectRaw('transaction_details.*, type_service_name, category_service_name, code, klinik.name as klinik')
            ->join('transaction','transaction.id','=','transaction_details.transaction_id','left')
            ->join('klinik','klinik.id','=','transaction.klinik_id','left')
            ->where('transaction_details.transaction_id', $getData->id)
            ->get();

        //dd($getTransaction);
        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;
        $data['transaction'] = $getTransaction;

        return view($this->listView[$data['viewType']], $data);
    }

}
