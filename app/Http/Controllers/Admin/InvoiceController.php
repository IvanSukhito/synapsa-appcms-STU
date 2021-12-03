<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class InvoiceController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'list' => 0,
            ],
            'product_category_id' => [
                'lang' => 'general.product-category',
                'type' => 'select2',
                'list' => 0,
                'show' => 0,
                'edit' => 0,
            ],
            'klinik_id' => [
                'lang' => 'general.klinik',
                'type' => 'select2',
                'list' => 0,
                'show' => 0,
                'edit' => 0,
            ],
            'transaction_date' => [
                'extra' => [
                    'edit' => ['disabled' => true],
                ],
            ],
            'product_category_name' => [
                'lang' => 'general.product-category',
                'extra' => [
                    'edit' => ['disabled' => true],
                ],
                'list' => 0,
            ],
            'klinik_name' => [
                'lang' => 'general.klinik',
                'extra' => [
                    'edit' => ['disabled' => true],
                ]
            ],
            'klinik_address' => [
                'list' => 0,
                'extra' => [
                    'edit' => ['disabled' => true],
                ]
            ],
            'klinik_no_telp' => [
                'list' => 0,
                'extra' => [
                    'edit' => ['disabled' => true],
                ]
            ],
            'klinik_email' => [
                'list' => 0,
                'extra' => [
                    'edit' => ['disabled' => true],
                ]
            ],
            'product_name' => [
                'extra' => [
                    'edit' => ['disabled' => true],
                ]
            ],
            'total_qty_transaction' => [
                'extra' => [
                    'edit' => ['disabled' => true],
                ],
            ],
            'price_nice_total_price_transaction' => [
                'extra' => [
                    'edit' => ['disabled' => true],
                ],
                'lang' => 'general.total_price_transaction',
                'custom' => ', name: "total_price_transaction"'
            ],
            'product_image_full' => [
                'list' => 0,
                'edit' => 0,
                'type' => 'image',
                'lang' => 'general.product_image'
            ],
            'price_nice_product_klinik' => [
                'extra' => [
                    'edit' => ['disabled' => true],
                ],
                'lang' => 'general.price_product_klinik',
                'custom' => ', name: "price_product_klinik"'
            ],
            'price_nice_product_synapsa' => [
                'extra' => [
                    'edit' => ['disabled' => true],
                ],
                'lang' => 'general.price_product_synapsa',
                'custom' => ', name: "price_product_synapsa"'
            ],
            'product_unit' => [
                'extra' => [
                    'edit' => ['disabled' => true],
                ],
                'list' => 0,
            ],
            'product_type' => [
                'extra' => [
                    'edit' => ['disabled' => true],
                ],
                'list' => 0,
                'type' => 'select',
            ],
            'status' => [
                'validate' => [
                    'edit' => 'required'
                ],
                'type' => 'select',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'Aksi',
            ]
        ];

        parent::__construct(
            $request, 'general.invoice', 'invoice', 'V1\Invoice', 'invoice',
            $passingData
        );

        $this->data['listSet']['status'] = get_list_status_invoice();
        $this->data['listSet']['stock_flag'] = get_list_stock_flag();
        $this->data['listSet']['product_type'] = get_list_type_product();

        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.invoice.list';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.invoice.show';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.invoice.forms';
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
            if (in_array($list['type'], ['select', 'select2', 'multiselect2'])) {
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName) {
                    $getList = isset($this->data['listSet'][$fieldName]) ? $this->data['listSet'][$fieldName] : [];
                    return isset($getList[$query->$fieldName]) ? $getList[$query->$fieldName] : $query->$fieldName;
                });
            }
            else if (in_array($list['type'], ['image', 'image_preview'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    if ($query->{$fieldName.'_full'}) {
                        return '<img src="' . $query->{$fieldName.'_full'}. '" class="img-responsive max-image-preview"/>';
                    }
                    return '<img src="' . asset($list['path'] . $query->$fieldName) . '" class="img-responsive max-image-preview"/>';
                });
            }
            else if (in_array($list['type'], ['code'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<pre>' . json_encode(json_decode($query->$fieldName, true), JSON_PRETTY_PRINT) . '</pre>';
                });
            }
            else if (in_array($list['type'], ['texteditor'])) {
                $listRaw[] = $fieldName;
            }

            if($fieldName == 'transaction_date') {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return date('d F Y', strtotime($query->$fieldName));
                });
            }

        }

        return $dataTables
            ->rawColumns($listRaw)
            ->addIndexColumn()
            ->make(true);
    }

    public function edit($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $getDescProduct = json_decode($getData->product_desc, true);

        $title = [];
        $desc = [];
        if($getDescProduct) {
            foreach ($getDescProduct as $index => $listProduct) {
                if(isset($listProduct['content'])) {
                    $title[] = $listProduct['title'];
                    $desc[] = $listProduct['content'];
                }
                else {
                    $title = $listProduct['title'];
                    $desc = $listProduct['desc'];
                }
            }

            $listProduct = ['title' => $title, 'desc' => $desc];
        }
        else {
            $listProduct = [
                $title[] = 'title' => [''],
                $desc[] = 'desc' => [''],
            ];
        }

        $getData['transaction_date'] = date('d F Y', strtotime($getData['transaction_date']));

        $data['viewType'] = 'edit';
        $data['formsTitle'] = __('general.title_edit', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['listProduct'] = $listProduct;
        $data['data'] = $getData;

        return view($this->listView[$data['viewType']], $data);
    }

    public function show($id)
    {
        $this->callPermission();

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $getDescProduct = json_decode($getData->product_desc, true);

        $title = [];
        $desc = [];
        if($getDescProduct) {
            foreach ($getDescProduct as $index => $listProduct) {
                if(isset($listProduct['content'])) {
                    $title[] = $listProduct['title'];
                    $desc[] = $listProduct['content'];
                }
                else {
                    $title = $listProduct['title'];
                    $desc = $listProduct['desc'];
                }
            }

            $listProduct = ['title' => $title, 'desc' => $desc];
        }
        else {
            $listProduct = [
                $title[] = 'title' => [''],
                $desc[] = 'desc' => [''],
            ];
        }

        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['listProduct'] = $listProduct;
        $data['data'] = $getData;

        return view($this->listView[$data['viewType']], $data);
    }

}
