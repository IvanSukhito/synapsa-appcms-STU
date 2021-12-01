<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Logic\agoraLogic;
use App\Codes\Logic\DoctorLogic;
use App\Codes\Logic\ProductLogic;
use App\Codes\Models\Settings;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\Doctor;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DoctorAppointmentController extends Controller
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

        $s = strip_tags($this->request->get('s'));
        $time = intval($this->request->get('time'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $doctorLogic = new DoctorLogic();
        $getData = $doctorLogic->appointmentListDoctor($user->id, $time, $s, $getLimit);
        if ($getData['success'] == 0) {
            return response()->json([
                'success' => 0,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        return response()->json([
            'success' => 1,
            'data' => $getData['data'],
            'default_image' => asset('assets/cms/images/no-img.png'),
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function detail($id)
    {
        $user = $this->request->attributes->get('_user');
        $doctorLogic = new DoctorLogic();
        $getData = $doctorLogic->appointmentInfo($id, $user->id);
        if ($getData['success'] != 80) {
            if ($getData['success'] == 90) {
                $message = 'Hanya menu untuk dokter';
            }
            else {
                $message = 'Janji Temu Dokter Tidak Ditemukan';
            }
            return response()->json([
                'success' => 0,
                'message' => [$message],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        return response()->json([
            'success' => 1,
            'data' => $getData['data'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function approveMeeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $doctorLogic = new DoctorLogic();
        $getResult = $doctorLogic->appointmentApprove($id, $user->id);
        if ($getResult != 80) {
            if ($getResult == 90) {
                $message = 'Hanya menu untuk dokter';
            }
            else {
                $message = 'Janji Temu Dokter Tidak Ditemukan';
            }
            return response()->json([
                'success' => 0,
                'message' => [$message],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        return response()->json([
            'success' => 1,
            'message' => ['Sukses Di Approve'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function reSchedule($id){

        $user = $this->request->attributes->get('_user');
        $message = strip_tags($this->request->get('message'));

        $doctorLogic = new DoctorLogic();
        $getResult = $doctorLogic->appointmentRequestReSchedule($id, $user->id, $message);
        if ($getResult != 80) {
            if ($getResult == 90) {
                $message = 'Hanya menu untuk dokter';
            }
            else {
                $message = 'Janji Temu Dokter Tidak Ditemukan';
            }
            return response()->json([
                'success' => 0,
                'message' => [$message],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        return response()->json([
            'success' => 1,
            'message' => ['Sukses Meminta Reschedule'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function meeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $doctorLogic = new DoctorLogic();
        $getResult = $doctorLogic->meetingCreate($id, $user->id);
        if ($getResult['success'] != 80) {
            return response()->json([
                'success' => 1,
                'message' => ['Hanya menu untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getData = $getResult['data'];

        return response()->json([
            'success' => 1,
            'data' => $getData,
            'message' => ['Sukses'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function cancelCallMeeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $doctorLogic = new DoctorLogic();
        $doctorLogic->meetingCallFailed($id, $user->id);

        return response()->json([
            'success' => 1,
            'message' => ['Sukses Di Tutup'],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function finishMeeting($id)
    {
        $user = $this->request->attributes->get('_user');

        $doctorLogic = new DoctorLogic();
        $getResult = $doctorLogic->meetingCallFinish($id, $user->id);
        if ($getResult) {
            return response()->json([
                'success' => 1,
                'message' => ['Sukses Selesai'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }
        else {
            return response()->json([
                'success' => 0,
                'message' => ['Gagal'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

    }

    public function doctorMedicine($id)
    {
        $user = $this->request->attributes->get('_user');
        $doctorLogic = new DoctorLogic();
        $getDoctor = $doctorLogic->checkDoctor($user->id);
        if (!$getDoctor) {
            return response()->json([
                'success' => 0,
                'message' => ['Menu ini hanya untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

        $s = strip_tags($this->request->get('s'));
        $categoryId = intval($this->request->get('category_id'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $productLogic = new ProductLogic();
        $getData = $productLogic->productGet($user->klinik_id, $getLimit, $categoryId, $s);

        return response()->json([
            'success' => 1,
            'data' => $getData,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function doctorDiagnosis($id)
    {
        $user = $this->request->attributes->get('_user');
        $doctorLogic = new DoctorLogic();
        $getDoctor = $doctorLogic->checkDoctor($user->id);
        if (!$getDoctor) {
            return response()->json([
                'success' => 0,
                'message' => ['Menu ini hanya untuk dokter'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

        $validator = Validator::make($this->request->all(), [
            'diagnosis' => 'required',
            'treatment' => 'required',
            'product_ids' => 'array',
            'qty' => 'array',
            'dose' => 'array',
            'type_dose' => 'array',
            'period' => 'array',
            'note' => 'array',
            'body_height' => '',
            'body_weight' => '',
            'blood_pressure' => '',
            'body_temperature' => '',
            'symptoms' => ''
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getDoctorPrescription = $this->request->file('doctor_prescription');

        $saveData = [
            'diagnosis' => $this->request->get('diagnosis'),
            'treatment' => $this->request->get('treatment'),
            'body_height' => $this->request->get('body_height'),
            'body_weight' => $this->request->get('body_weight'),
            'blood_pressure' => $this->request->get('blood_pressure'),
            'body_temperature' => $this->request->get('body_temperature'),
            'symptoms' => $this->request->get('symptoms')
        ];

        $getProductIds = $this->request->get('product_ids');
        $getListQty = $this->request->get('qty');
        $getListDose = $this->request->get('dose');
        $getListTypeDose = $this->request->get('type_dose');
        $getListPeriod = $this->request->get('period');
        $getListNote = $this->request->get('note');
        $saveProducts = [];

        $getProducts = Product::whereIn('id', $getProductIds)->get();
        $listProduct = [];
        foreach ($getProducts as $getProduct) {
            $listProduct[$getProduct->id] = $getProduct;
        }

        foreach ($getProductIds as $index => $productId) {
            $getProduct = $listProduct[$productId] ?? false;
            $getQty = isset($getListQty[$index]) ? intval($getListQty[$index]) : 1;
            $getDose = $getListDose[$index] ?? '';
            $getTypeDose = $getListTypeDose[$index] ?? '';
            $getPeriod = $getListPeriod[$index] ?? '';
            $getNote = $getListNote[$index] ?? '';
            $saveProducts[] = [
                'appointment_doctor_id' => $id,
                'product_id' => $productId,
                'product_name' => $getProduct ? $getProduct->name : '',
                'product_qty' => $getQty,
                'product_price' => $getProduct ? $getProduct->price : 0,
                'dose' => $getDose,
                'type_dose' => $getTypeDose,
                'period' => $getPeriod,
                'note' => $getNote
            ];
        }

        $getResult = $doctorLogic->appointmentDiagnosis($id, $getDoctor->id, $saveData, $saveProducts, $getDoctorPrescription);
        if (!$getResult) {
            return response()->json([
                'success' => 0,
                'message' => ['Janji Temu Dokter Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        $getPatientId = $getResult->user_id;

        $getPatient = Users::where('id', $getPatientId)->first();
        $getFcmTokenPatient = [];
        if ($getPatient) {
            $getFcmTokenPatient = $getPatient->getDeviceToken()->pluck('token')->toArray();
        }
        $getDoctor = Users::where('id', $user->id)->first();
        $getFcmTokenDoctor = [];
        if ($getPatient) {
            $getFcmTokenDoctor = $getDoctor->getDeviceToken()->pluck('token')->toArray();
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'appointment' => $getResult,
                'doctor_name' => $user->fullname,
                'fcm_token_patient' => $getFcmTokenPatient,
                'fcm_token_doctor' => $getFcmTokenDoctor,
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

}
