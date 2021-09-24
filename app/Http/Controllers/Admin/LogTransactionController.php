<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\District;
use App\Codes\Models\V1\Klinik;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\ProductCategory;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\SubDistrict;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class LogTransactionController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'transaction_id' => [
                'lang' => 'general.transaction',
            ],
            'old_data' => [
                'lang' => 'general.transaction',
            ],
            'new_data' => [
                'lang' => 'general.transaction',
            ],
            'reason' => [
                'lang' => 'general.transaction',
            ],
            'service' => [
                'lang' => 'general.transaction',
            ],
            'type_payment' => [
                'lang' => 'general.transaction',
            ],
            'status' => [
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
            $request, 'general.transaction', 'transaction', 'V1\Transaction', 'transaction',
            $passingData
        );


    }


}
