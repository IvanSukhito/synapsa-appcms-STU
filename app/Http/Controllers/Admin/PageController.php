<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Codes\Logic\_CrudController;

class PageController extends _CrudController
{
    protected $passingDataWinner;
    protected $passingDataPopup;

    protected $passingDataHome;
    protected $passingDataAbout;
    protected $passingDataCoach;
    protected $passingDataReward;
    protected $passingDataFooter;

    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'list' => 0
            ],
            'name' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.name',
            ],
            'key' => [
                'edit' => 0,
                'lang' => 'general.page',
            ],
            'header_image' => [
                'lang' => 'general.image',
                'type' => 'image',

            ],
            'content' => [
                'list' => 0,
                'lang' => 'general.content',
                'type' => 'texteditor',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'custom' => ',orderable:false'
            ],
        ];

        parent::__construct(
            $request, 'general.page', 'page', 'V1\Page', 'page',
            $passingData
        );
    }
}
