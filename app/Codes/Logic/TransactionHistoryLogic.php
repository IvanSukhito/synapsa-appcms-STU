<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionHistoryLogic
{
    public function __construct()
    {
    }

    /**
     * @param $userId
     * @param $limit
     * @param $search
     * @return array
     */
    public function productHistory($userId, $limit, $search): array
    {
        $getData = Transaction::selectRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name, transaction.total_qty, transaction.subtotal, transaction.total,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
            MIN(product_name) AS product_name, MIN(product.image) as image,
            CONCAT("'.env('OSS_URL').'/'.'", MIN(product.image)) AS image_full')
            ->join('transaction_details', function($join){
                $join->on('transaction_details.transaction_id','=','transaction.id')
                    ->on('transaction_details.id', '=', DB::raw("(select min(id) from transaction_details WHERE transaction_details.transaction_id = transaction.id)"));
            })
            ->join('product', function($join){
                $join->on('product.id','=','transaction_details.product_id')
                    ->on('product.id', '=', DB::raw("(select min(id) from product WHERE product.id = transaction_details.product_id)"));
            })
            ->where('transaction.user_id', $userId)
            ->whereIn('type_service', [1,5])->orderBy('transaction.id','DESC');

        if (strlen($search) > 0) {
            $getData = $getData->where('code', 'LIKE', "%$search%")->orWhere('product_name', 'LIKE', "%$search%");
        }

        $getData = $getData->orderBy('transaction.id','DESC')
            ->groupByRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name, transaction.total_qty, transaction.subtotal, transaction.total,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status')->paginate($limit);

        return [
            'data' => $getData,
            'default_image' => asset('assets/cms/images/no-img.png')
        ];

    }

    /**
     * @param $userId
     * @param $serviceId
     * @param $limit
     * @param $search
     * @return array
     */
    public function doctorHistory($userId, $serviceId, $limit, $search): array
    {
        $doctorLogic = new DoctorLogic();
        $getService = $doctorLogic->getListService($serviceId);

        $getData = Transaction::selectRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
            doctor_category.name as category_doctor, MIN(doctor_name) AS doctor_name,
            MIN(users.image) AS image, CONCAT("'.env('OSS_URL').'/'.'", MIN(users.image)) AS image_full')
            ->leftJoin('transaction_details', 'transaction_details.transaction_id','=','transaction.id')
            ->leftJoin('doctor', 'doctor.id','=','transaction_details.doctor_id')
            ->leftJoin('doctor_category','doctor_category.id','=','doctor.doctor_category_id')
            ->leftJoin('users', 'users.id','=','doctor.user_id')
            ->where('transaction.user_id', $userId)
            ->where('type_service', 2);

        if ($serviceId > 0) {
            $getData = $getData->where('category_service_id', $serviceId);
        }

        if (strlen($search) > 0) {
            $getData = $getData->where('code', 'LIKE', "%$search%")->orWhere('doctor_name', 'LIKE', "%$search%");
        }

        $getData = $getData->orderBy('transaction.id','DESC')
            ->groupByRaw('transaction.id, transaction.created_at,
                transaction.category_service_id, transaction.category_service_name,
                transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
                category_doctor')
            ->paginate($limit);

        return [
            'data' => $getData,
            'service' => $getService['data'],
            'sub_service' => $getService['sub_service'],
            'default_image' => asset('assets/cms/images/no-img.png')
        ];

    }

    /**
     * @param $userId
     * @param $serviceId
     * @param $limit
     * @param $s
     * @return array
     */
    public function labHistory($userId, $serviceId, $limit, $search): array
    {
        $labLogic = new LabLogic();
        $getService = $labLogic->getListService($serviceId);

        $getData = Transaction::selectRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status,
            MIN(lab_name) AS lab_name, MIN(lab.image) as image, CONCAT("'.env('OSS_URL').'/'.'", MIN(lab.image)) AS image_full')
            ->join('transaction_details', function($join){
                $join->on('transaction_details.transaction_id','=','transaction.id')
                    ->on('transaction_details.id', '=', DB::raw("(select min(id) from transaction_details WHERE transaction_details.transaction_id = transaction.id)"));
            })
            ->join('lab', function($join){
                $join->on('lab.id','=','transaction_details.lab_id')
                    ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = transaction_details.lab_id)"));
            })
            ->where('transaction.user_id', $userId)
            ->where('type_service', 3);

        if ($serviceId > 0) {
            $getData = $getData->where('category_service_id', $serviceId);
        }

        if (strlen($search) > 0) {
            $getData = $getData->where('code', 'LIKE', "%$search%")->orWhere('lab_name', 'LIKE', "%$search%");
        }

        $getData = $getData->orderBy('transaction.id','DESC')
            ->groupByRaw('transaction.id, transaction.created_at,
            transaction.category_service_id, transaction.category_service_name,
            transaction.type_service, transaction.type_service_name, transaction.user_id, transaction.status')->paginate($limit);

        return [
            'data' => $getData,
            'service' => $getService['data'],
            'sub_service' => $getService['sub_service'],
            'default_image' => asset('assets/cms/images/no-img.png')
        ];

    }

}
