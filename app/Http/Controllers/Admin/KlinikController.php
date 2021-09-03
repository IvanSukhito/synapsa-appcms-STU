<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\Klinik;
use Illuminate\Http\Request;

class KlinikController extends _CrudController
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
                ]
            ],
            'status' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
            ],
        ];

        parent::__construct(
            $request, 'general.klinik', 'klinik', 'V1\Klinik', 'klinik',
            $passingData
        );

        $this->data['listSet']['status'] = get_list_status();
    }
}
