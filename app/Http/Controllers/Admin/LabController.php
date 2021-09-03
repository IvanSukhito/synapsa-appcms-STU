<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\Lab;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LabController extends _CrudController
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
            'price' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'thumbnail_img' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'image',
                'lang' => 'general.thumbnail'
            ],
            'image' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'image',
            ],
            'desc_lab' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'textarea',
            ],
            'desc_benefit' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'textarea'
            ],
            'desc_preparation' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'textarea'
            ],
            'recommended_for' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'json',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'Aksi',
            ]
        ];

        parent::__construct(
            $request, 'general.lab', 'lab', 'V1\Lab', 'lab',
            $passingData
        );
    }

    public function dataTable()
    {
        $this->callPermission();

        $dataTables = new DataTables();

        $builder = $this->model::query()->select('*');

        $dataTables = $dataTables->eloquent($builder)
            ->addColumn('action', function ($query) {
                return view($this->listView['dataTable'], [
                    'query' => $query,
                    'thisRoute' => $this->route,
                    'permission' => $this->permission,
                    'masterId' => $this->masterId
                ]);
            });

        $listRaw = [];
        $listRaw[] = 'action';
        foreach (collectPassingData($this->passingData) as $fieldName => $list) {
            if (in_array($list['type'], ['select', 'select2'])) {
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName) {
                    $getList = isset($this->data['listSet'][$fieldName]) ? $this->data['listSet'][$fieldName] : [];
                    return isset($getList[$query->$fieldName]) ? $getList[$query->$fieldName] : $query->$fieldName;
                });
            }
            else if (in_array($list['type'], ['image', 'image_preview'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<img src="' . asset($list['path'] . $query->$fieldName) . '" class="img-responsive max-image-preview"/>';
                });
            }
            else if (in_array($list['type'], ['code'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<pre>' . json_encode(json_decode($query->$fieldName, true), JSON_PRETTY_PRINT) . '</pre>';
                });
            }
            else if (in_array($list['type'], ['json'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return isset($getList[$query->$fieldName]) ? json_decode([$query->$fieldName]) : json_decode($query->$fieldName);
                });
            }
            else if (in_array($list['type'], ['texteditor'])) {
                $listRaw[] = $fieldName;
            }
        }

        return $dataTables
            ->rawColumns($listRaw)
            ->make(true);
    }
}
