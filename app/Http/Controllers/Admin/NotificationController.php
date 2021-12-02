<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotificationController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'user_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
                'lang' => 'general.user'
            ],
            'title' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.title',
            ],
            'message' => [
                'type' => 'texteditor',
                'lang' => 'general.message',
            ],
            'type' => [
                'lang' => 'general.type'
            ],
            'target_id' => [
            ],
            'target_menu' => [
            ],
            'date' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'datepicker',
                'lang' => 'general.date'
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'custom' => ',orderable:false'
            ]
        ];

        parent::__construct(
            $request, 'general.notification', 'notification', 'V1\Notifications', 'notification',
            $passingData
        );

        $getUsers = Users::where('status', 80)->pluck('fullname', 'id')->toArray();
        if($getUsers) {
            foreach($getUsers as $key => $value) {
                $listUsers[$key] = $value;
            }
        }

        $this->data['listSet']['user_id'] = $listUsers;

    }

}
