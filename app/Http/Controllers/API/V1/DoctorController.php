<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\DoctorSchedule;
use App\Codes\Models\V1\DoctorCategory;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\TempAd;
use App\Codes\Models\V1\TempAdDetail;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DoctorController extends Controller
{
    protected $request;
    protected $setting;
    protected $limit;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setting = Cache::remember('setting', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
        $this->limit = 5;
    }

    public function getDoctor(){

        $user = $this->request->attributes->get('_user');

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $service = Service::orderBy('orders', 'ASC')->get();
        $category = DoctorCategory::orderBy('orders', 'ASC')->get();

        $getInterestService = $user->interest_service_id;
        $getInterestCategory = $user->interest_category_id;
        if ($getInterestService <= 0) {
            foreach ($service as $index => $list) {
                if ($index == 0) {
                    $getInterestService = $list->id;
                }
            }
        }
        if ($getInterestCategory <= 0) {
            foreach ($category as $index => $list) {
                if ($index == 0) {
                    $getInterestCategory = $list->id;
                }
            }
        }

        $data = Users::selectRaw('doctor.id, users.fullname as doctor_name, image, doctor.price, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->join('doctor_service', 'doctor_service.doctor_id','=','doctor.id')
            ->where('doctor.doctor_category_id','=', $getInterestCategory)
            ->where('doctor_service.service_id','=', $getInterestService)
            ->where('users.doctor','=', 1);

        if (strlen($s) > 0) {
            $data = $data->where('users.fullname', 'LIKE', "%$s%");
        }

        $data = $data->orderBy('users.fullname', 'ASC')->paginate($getLimit);

        return response()->json([
            'success' => 1,
            'data' => [
                'doctor' => $data,
                'service' => $service,
                'category' => $category,
                'active' => [
                    'service' => $getInterestService,
                    'category' => $getInterestCategory
                ]
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function doctorCategory()
    {
        $category = DoctorCategory::orderBy('orders', 'ASC')->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'category' => $category,
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function getDoctorDetail($id){

        $user = $this->request->attributes->get('_user');

        $data = Users::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->where('doctor.id', '=', $id)
            ->where('users.doctor','=', 1)->first();

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Doctor Not Found'],
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

    public function listBookDoctor($id)
    {
        $serviceId = $this->request->get('service_id');

        $data = Users::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->where('doctor.id', '=', $id)
            ->where('users.doctor','=', 1)->first();

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Doctor Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else {

            $getDoctorSchedule = DoctorSchedule::where('doctor_id', '=', $id)->where('service_id', '=', $serviceId)
                ->where('date_available', '>=', date('Y-m-d'))
                ->get();

            return response()->json([
                'success' => 1,
                'data' => [
                    'schedule' => $getDoctorSchedule,
                    'doctor' => $data
                ],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);

        }
    }

    public function checkSchedule($id)
    {
        $getDoctorSchedule = DoctorSchedule::where('id', '=', $id)->first();

        if (!$getDoctorSchedule) {

            return response()->json([
                'success' => 0,
                'message' => ['Schedule Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);

        }
        else if ($getDoctorSchedule->status != 80) {
            return response()->json([
                'success' => 0,
                'message' => ['Schedule Already Book'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'schedule' => $getDoctorSchedule,
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);
    }

    public function scheduleSummary($id)
    {
        $getDoctorSchedule = DoctorSchedule::where('id', '=', $id)->where('status', '=', 80)->first();
        if (!$getDoctorSchedule) {
            return response()->json([
                'success' => 0,
                'message' => ['Schedule Not Found or Already Book'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data = Users::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->where('doctor.id', '=', $getDoctorSchedule->doctor_id)
            ->where('users.doctor','=', 1)->first();

        return response()->json([
            'success' => 1,
            'data' => [
                'schedule' => $getDoctorSchedule,
                'doctor' => $data
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getPayment($id)
    {
        $getDoctorSchedule = DoctorSchedule::where('id', '=', $id)->where('status', '=', 80)->first();
        if (!$getDoctorSchedule) {
            return response()->json([
                'success' => 0,
                'message' => ['Schedule Not Found or Already Book'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data = Users::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->where('doctor.id', '=', $getDoctorSchedule->doctor_id)
            ->where('users.doctor','=', 1)->first();

        $getPayment = Payment::where('status', 80)->get();

        return response()->json([
            'success' => 1,
            'data' => [
                'schedule' => $getDoctorSchedule,
                'doctor' => $data,
                'payment' => $getPayment
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function checkout($id)
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'payment_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
            ], 422);
        }

        $paymentId = $this->request->get('payment_id');

        $getDoctorSchedule = DoctorSchedule::where('id', '=', $id)->where('status', '=', 80)->first();
        if (!$getDoctorSchedule) {
            return response()->json([
                'success' => 0,
                'message' => ['Schedule Not Found or Already Book'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $data = Users::selectRaw('doctor.id, users.fullname as doctor_name, image, address, address_detail, pob, dob,
            phone, gender, doctor.price, doctor.formal_edu, doctor.nonformal_edu, doctor_category.name as category')
            ->join('doctor', 'doctor.user_id', '=', 'users.id')
            ->join('doctor_category', 'doctor_category.id','=','doctor.doctor_category_id')
            ->where('doctor.id', '=', $getDoctorSchedule->doctor_id)
            ->where('users.doctor','=', 1)->first();

        $getPayment = Payment::where('id', $paymentId)->first();
        $paymentInfo = [];

        $subTotal = $data->price;
        $total = $subTotal;

        $getTotal = Transaction::where('klinik_id', $user->klinik_id)->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))->count();

        $newCode = date('Ym').str_pad(($getTotal + 1), 6, '0', STR_PAD_LEFT);

        DB::beginTransaction();

        $getTransaction = Transaction::create([
            'klinik_id' => $user->klinik_id,
            'user_id' => $user->id,
            'code' => $newCode,
            'payment_id' => $paymentId,
            'payment_name' => $getPayment->name,
            'type' => 2,
            'subtotal' => $subTotal,
            'total' => $total,
            'status' => 1
        ]);

        $getTransactionDetails = TransactionDetails::create([
            'transaction_id' => $getTransaction->id,
            'schedule_id' => $id,
            'doctor_id' => $data->id,
            'doctor_name' => $data->full_name,
            'doctor_price' => $subTotal
        ]);

        DoctorSchedule::where('id', $id)->update([
            'book' => 99
        ]);

        DB::commit();

        return response()->json([
            'success' => 1,
            'data' => [
                'checkout_info' => $getTransaction,
                'checkout_details' => $getTransactionDetails,
                'payment_info' => $paymentInfo
            ],
            'message' => ['Success'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

}
