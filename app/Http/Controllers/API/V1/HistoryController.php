<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\SynapsaLogic;
use App\Codes\Logic\TransactionHistoryLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\LogServiceTransaction;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class HistoryController extends Controller
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

    public function index()
    {
        $user = $this->request->attributes->get('_user');

        $getServiceId = intval($this->request->get('service_id'));
        $getSubServiceId = intval($this->request->get('sub_service_id'));
        $getDoctor = intval($this->request->get('doctor'));
        $getLab = intval($this->request->get('lab'));
        $getNurse = intval($this->request->get('nurse'));
        $s = strip_tags($this->request->get('s'));
        $getLimit = intval($this->request->get('limit'));

        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $transactionHistoryLogic = new TransactionHistoryLogic();

        if ($getDoctor == 1) {
            $getData = $transactionHistoryLogic->doctorHistory($user->id, $getServiceId, $getSubServiceId, $getLimit, $s);
            $getProduct = 0;
            $getLab = 0;
            $getNurse = 0;
        }
        elseif ($getLab == 1) {
            $getData = $transactionHistoryLogic->labHistory($user->id, $getServiceId, $getSubServiceId, $getLimit, $s);
            $getProduct = 0;
            $getDoctor = 0;
            $getNurse = 0;
        }
        elseif ($getNurse == 1){
            $getData = $this->getListNurse($user->id, $getLimit);
            $getDoctor = 0;
            $getLab = 0;
            $getProduct = 0;
        }
        else {
            $getData = $transactionHistoryLogic->productHistory($user->id, $getLimit, $s);
            $getProduct = 1;
            $getDoctor = 0;
            $getLab = 0;
            $getNurse = 0;
        }

        $active = $getData['active'] ?? [];
        $active['doctor'] = $getDoctor;
        $active['product'] = $getProduct;
        $active['lab'] = $getLab;
        $active['nurse'] = $getNurse;

        $getService = $getData['service'] ?? [];
        $getSubService = $getData['sub_service'] ?? [];
        if ($getService) {
            $temp = [[
                'id' => 0,
                'name' => 'Semua',
                'type' => 0,
                'type_nice' => '',
                'active' => $getServiceId == 0 ? 1 : 0
            ]];
            foreach ($getService as $list) {
                $temp[] = $list;
            }
            $getService = $temp;
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'data' => $getData['data'],
                'service' => $getService,
                'sub_service' => $getSubService,
                'default_image' => $getData['default_image'] ?? '',
                'active' => $active
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function detail($id)
    {
        $user = $this->request->attributes->get('_user');

        $transactionHistoryLogic = new TransactionHistoryLogic();
        $getData = $transactionHistoryLogic->detailHistory($user->id, $id);
        if ($getData['success'] == 0) {
            return response()->json([
                'success' => 0,
                'message' => ['Tipe Riwayat Transaksi Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => $getData['data'],
            'message' => ['Berhasil'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function rePayment($id)
    {
        $user = $this->request->attributes->get('_user');

        $transactionHistoryLogic = new TransactionHistoryLogic();
        $getData = $transactionHistoryLogic->reTriggerPayment($user->id, $id);
        if ($getData['success'] != 80) {
            switch ($getData['success']) {
                case 91: $message = 'Riwayat Transaksi Tidak Menggunakan E-Wallet atau QRis';
                    break;
                case 92: $message = 'Riwayat Transaksi Sudah Tidak boleh Re Payment';
                    break;
                default: $message = 'Riwayat Transaksi Tidak Ditemukan';
                    break;
            }
            return response()->json([
                'success' => 0,
                'message' => [$message],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => $getData['data'],
            'message' => ['Berhasil'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    private function getListNurse($userId, $getLimit)
    {
        $result = Transaction::selectRaw('transaction.id, transaction.created_at,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status, nurse_booked, nurse_shift as shift_qty')
            ->join('transaction_details','transaction_details.transaction_id','=','transaction.id')
            ->where('transaction.user_id', $userId)
            ->where('type_service', 4)
            ->orderBy('transaction.id','DESC');

        $getData = $result->groupByRaw('transaction.id, transaction.created_at,
                    transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
                    nurse_booked , shift_qty')
                    ->paginate($getLimit);

        return [
            'data' => $getData,
            'default_image' => asset('assets/cms/images/no-img.png')
        ];

    }

}
