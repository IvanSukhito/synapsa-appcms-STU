<?php

namespace App\Codes\Logic;

class PushNotification
{
    public function __construct()
    {

    }

    public function send($title, $body, $registrationIds)
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
