<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentLab;
use App\Codes\Models\V1\AppointmentNurse;
use App\Codes\Models\V1\DeviceToken;
use App\Codes\Models\V1\Notifications;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\Users;
use Illuminate\Support\Facades\DB;

class PushNotification
{
    public function __construct()
    {

    }

    public function sendingNotification($userIds, $title, $message, $target = array())
    {
        $getUserIds = Users::whereIn('id', $userIds)->pluck('id')->toArray();
        if (count($getUserIds) > 0) {
            $dateNow = date('Y-m-d H:i:s');
            DB::beginTransaction();

            $saveNotification = [];
            foreach ($getUserIds as $getUserId) {
                $saveNotification[] = [
                    'user_id' => $getUserId,
                    'title' => $title,
                    'message' => $message,
                    'type' => $target['type'] ?? 0,
                    'target_menu' => $target['target_menu'] ?? '',
                    'target_id' => $target['target_id'] ?? 0,
                    'is_read' => 1,
                    'date' => $dateNow,
                    'created_at' => $dateNow,
                    'updated_at' => $dateNow
                ];
            }

            Notifications::insert($saveNotification);

            DB::commit();

            $getDeviceToken = DeviceToken::join('user_device_token', 'user_device_token.device_token_id', '=', 'device_token.id')
                ->whereRaw('LENGTH(token) > 5')->whereIn('user_id', $getUserIds)->pluck('token')->toArray();

            if (count($getDeviceToken) > 0) {
                $this->send($title, $message, $getDeviceToken);
            }

            return 1;

        }

        return 0;

    }

    /**
     * @param int $minute
     */
    public function checkMeeting(int $minute = 5)
    {
        $dateNow = date('Y-m-d');
        $time = date('H:i:00', strtotime("+".$minute." minute"));
//        Log::info("$dateNow, $time, ".date('Y-m-d H:i:s'));
        $getServiceIds = Service::where('type', 1)->pluck('id')->toArray();

        $data = AppointmentDoctor::selectRaw('doctor.user_id as janji_id, appointment_doctor.user_id')
            ->join('doctor','doctor.id','=','appointment_doctor.doctor_id')
            ->where('appointment_doctor.date', '=', $dateNow)
            ->where('appointment_doctor.time_start', '=', $time)
            ->whereIn('appointment_doctor.service_id', $getServiceIds)
            ->whereIn('appointment_doctor.status', [3,4])
            ->union(
                AppointmentLab::selectRaw('0 AS janji_id, appointment_lab.user_id')
                    ->join('appointment_lab_details', function($join){
                        $join->on('appointment_lab_details.appointment_lab_id','=','appointment_lab.id')
                            ->on('appointment_lab_details.id', '=', DB::raw("(select min(id) from appointment_lab_details WHERE appointment_lab_details.appointment_lab_id = appointment_lab.id)"));
                    })
                    ->join('lab', function($join){
                        $join->on('lab.id','=','appointment_lab_details.lab_id')
                            ->on('lab.id', '=', DB::raw("(select min(id) from lab WHERE lab.id = appointment_lab_details.lab_id)"));
                    })
                    ->where('appointment_lab.time_start', '=', $time)
                    ->whereIn('appointment_lab.service_id', $getServiceIds)
                    ->whereIn('appointment_lab.status', [3,4])
            )
            ->union(
                AppointmentNurse::selectRaw('0 AS janji_id, appointment_nurse.user_id')
                    ->where('appointment_nurse.time_start', '=', $time)
                    ->whereIn('appointment_nurse.service_id', $getServiceIds)
                    ->whereIn('appointment_nurse.status', [3,4])
            );

        $userIds = [];
        foreach ($data as $list) {
            if ($list->janji_id > 0) {
                $userIds[] = $list->janji_id;
            }
            if ($list->user_id > 0) {
                $userIds[] = $list->user_id;
            }
        }

        if (count($userIds) > 0) {
            $getDeviceToken = DeviceToken::join('user_device_token', 'user_device_token.device_token_id', '=', 'device_token.id')
                ->whereIn('user_device_token.user_id', $userIds)
                ->pluck('token')->toArray();

            if (count($getDeviceToken) > 0) {
                $logicPushNotification = new PushNotification();
                $logicPushNotification->send('Meeting di mulai 5 menit dari sekarang', 'Meeting akan di mulai 5 menit lagi', $getDeviceToken);
            }

        }

    }

    /**
     * @param $title
     * @param $body
     * @param $registrationIds
     * @return array
     */
    public function send($title, $body, $registrationIds): array
    {
        try {

            $msg = [
                'body' 	=> $body,
                'title'	=> $title
            ];

            $fields = [
                'registration_ids' => $registrationIds,
                'notification'	=> $msg,
                'data' => [
                    'extra'=>json_encode(
                        [
                            'title' => $msg['title'],
                            'body' => $msg['body'],
                            'flag' => 'notification'
                        ]
                    )
                ]
            ];

            $headers = [
                'Authorization: key=' . env('API_GOOGLE_ACCESS_KEY'),
                'Content-Type: application/json'
            ];

            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
//            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2);
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
            $result = curl_exec($ch );
            curl_close( $ch );

            LogPushNotification::create([
                'params' => json_encode($fields),
                'title' => $title,
                'body' => $body,
                'response' => is_string($result) ? $result : json_encode($result),
            ]);

            return [
                'success' => 1,
                'message' => 'Success'
            ];
        }
        catch (\Exception $e) {
            return [
                'success' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

}
