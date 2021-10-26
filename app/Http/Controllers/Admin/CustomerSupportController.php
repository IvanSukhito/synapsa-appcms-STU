<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\CustomerSupport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerSupportController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'name' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
           ],
            'contact' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
           ],
            'status' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
            ],
            'type' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'custom' => ',orderable:false'
            ]
        ];

        parent::__construct(
            $request, 'general.customer-support', 'customer-support', 'V1\CustomerSupport', 'customer-support',
            $passingData
        );

        $this->data['listSet']['status'] = get_list_active_inactive();
        $this->data['listSet']['type'] = get_list_type_support();

    }

}
