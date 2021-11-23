<?php

namespace App\Http\Controllers\Admin;


use App\Codes\Logic\generateLogic;
use App\Codes\Models\Admin;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $data;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->data = [
            'thisLabel' => 'Dashboard',
            'thisRoute' => 'dashboard',
        ];
    }

    public function dashboard()
    {
        $data = $this->data;

        $getRoleClinic = session()->get('admin_role_clinic');
        $getRoleAdmin = session()->get('admin_super_admin');
        //dd($getRoleAdmin);
        $adminId = session()->get('admin_id');
        $getClinic = Admin::where('id', $adminId)->first();


        //dd($getRoleClinic);

        if($getRoleClinic == 1) {
            if ($this->request->get('daterange')) {
                $getDateRange = $this->request->get('daterange');
                $dateSplit = explode(' | ', $getDateRange);
                $dateStart = date('Y-m-d 00:00:00', strtotime($dateSplit[0]));
                $dateEnd = isset($dateSplit[1]) ? date('Y-m-d 23:59:59', strtotime($dateSplit[1])) : date('Y-m-d 23:59:59', strtotime($dateSplit[0]));

                $data['clinic'] = Klinik::where('id', $getClinic->klinik_id)->first();
                $data['user'] = Users::where('klinik_id', $getClinic->klinik_id)->where('patient',1)->where('status',80)->get();
                $data['transaction'] = Transaction::where('klinik_id', $getClinic->klinik_id)->whereBetween('created_at', [$dateStart, $dateEnd]);
                $data['transactionDoctor'] = Transaction::where('klinik_id', $getClinic->klinik_id)->where('type_service', 2)->whereBetween('created_at', [$dateStart, $dateEnd]);
                $data['transactionLab'] = Transaction::where('klinik_id', $getClinic->klinik_id)->where('type_service', 3)->whereBetween('created_at', [$dateStart, $dateEnd]);
                $data['transactionProduct'] = Transaction::where('klinik_id', $getClinic->klinik_id)->where('type_service', 1)->whereBetween('created_at', [$dateStart, $dateEnd]);

            }
            else {
                $data['clinic'] = Klinik::where('id', $getClinic->klinik_id)->first();
                $data['user'] = Users::where('klinik_id', $getClinic->klinik_id)->where('patient',1)->where('status',80)->get();
                $data['transaction'] = Transaction::where('klinik_id', $getClinic->klinik_id);
                $data['transactionDoctor'] = Transaction::where('klinik_id', $getClinic->klinik_id)->where('type_service', 2);
                $data['transactionLab'] = Transaction::where('klinik_id', $getClinic->klinik_id)->where('type_service', 3);
                $data['transactionProduct'] = Transaction::where('klinik_id', $getClinic->klinik_id)->where('type_service', 1);
            }


            return view(env('ADMIN_TEMPLATE') . '.page.dashboard_clinic', $data);
        }
        elseif($getRoleAdmin == 1){
            $status_transaction = [];
            foreach(get_list_transaction() as $key => $val) {
                if(in_array($key, [1,2,3,80,81,82,90,99])) {
                    $status_transaction[$key] = $val;
                }
            }

            if ($this->request->get('daterange')) {
                $getDateRange = $this->request->get('daterange');
                $dateSplit = explode(' | ', $getDateRange);
                $dateStart = date('Y-m-d 00:00:00', strtotime($dateSplit[0]));
                $dateEnd = isset($dateSplit[1]) ? date('Y-m-d 23:59:59', strtotime($dateSplit[1])) : date('Y-m-d 23:59:59', strtotime($dateSplit[0]));

                $data['statusTransaction'] = $status_transaction;
                $data['clinic'] = Klinik::where('status', 80)->get();
                $data['userPatient'] = Users::where('patient',1)->where('status',80)->get();
                $data['userDoctor'] = Users::where('doctor',1)->where('status',80)->get();
                $data['transaction'] = Transaction::whereBetween('created_at', [$dateStart, $dateEnd])->get();
                $data['transactionDoctor'] = Transaction::where('type_service', 2)->whereBetween('created_at', [$dateStart, $dateEnd])->get();
                $data['transactionLab'] = Transaction::where('type_service', 3)->whereBetween('created_at', [$dateStart, $dateEnd])->get();
                $data['transactionProduct'] = Transaction::where('type_service', 1)->whereBetween('created_at', [$dateStart, $dateEnd])->get();
                $data['appointmentLab'] = AppointmentLab::whereBetween('created_at', [$dateStart, $dateEnd])->get();
                $data['appointmentDoctor'] = AppointmentDoctor::whereBetween('created_at', [$dateStart, $dateEnd])->get();
            }
            else {
                $data['statusTransaction'] = $status_transaction;
                $data['clinic'] = Klinik::where('status', 80)->get();
                $data['userPatient'] = Users::where('patient',1)->where('status',80)->get();
                $data['userDoctor'] = Users::where('doctor',1)->where('status',80)->get();
                $data['transaction'] = Transaction::get();
                $data['transactionDoctor'] = Transaction::where('type_service', 2)->get();
                $data['transactionLab'] = Transaction::where('type_service', 3)->get();
                $data['transactionProduct'] = Transaction::where('type_service', 1)->get();
                $data['appointmentLab'] = AppointmentLab::get();
                $data['appointmentDoctor'] = AppointmentDoctor::get();

            }
            return view(env('ADMIN_TEMPLATE') . '.page.dashboard_super_admin', $data);
        }

        return view(env('ADMIN_TEMPLATE').'.page.dashboard', $data);
    }

    public function download()
    {
        $data = AppointmentDoctor::where('id', 6)->first();
        if ($data && $data->status == 80) {
            $generateLogic = new generateLogic();
            $generateLogic->generatePdfDiagnosa($data);
            exit;
        }

        return '';
    }

}
