<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Admin;
use App\Codes\Models\V1\City;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ProductClinicController extends _CrudController
{
    protected $passingDataAddFromSynapsa;

    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'product_category_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.product-category',
                'type' => 'select2',
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
                'type' => 'money',

            ],
            'unit' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
            ],
            'image_full' => [
                'validate' => [
                    'create' => 'required',
                ],
                'type' => 'image',
                'list' => 0,
                //'lang' => 'image'
            ],
            'stock' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'number',
                'create' => 0,
                'edit' => 0,
                'show' => 0,
            ],
            'stock_flag' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
                'list' => 0,
                'create' => 0,
                'edit' => 0,
                'show' => 0,
            ],
            'type' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
            ],
            'status' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'Aksi',
            ]
        ];

        $this->passingDataAddFromSynapsa = generatePassingData([
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'product_category_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'lang' => 'general.product-category',
                'type' => 'select2',
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
                'type' => 'money',

            ],
            'unit' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'list' => 0,
            ],
            'image_full' => [
                'validate' => [
                    'create' => 'required',
                ],
                'type' => 'image',
                'list' => 0,
            ],
            'type' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select',
            ],
            'status' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'lang' => 'Aksi',
            ]
        ]);

        parent::__construct(
            $request, 'general.product_clinic', 'product-clinic', 'V1\Product', 'product-clinic',
            $passingData
        );

        $listCategory = [];
        $getCategory = ProductCategory::where('status', 80)->pluck('name', 'id')->toArray();
        if($getCategory) {
            foreach($getCategory as $key => $value) {
                $listCategory[$key] = $value;
            }
        }

        $this->data['listSet']['product_category_id'] = $listCategory;
        $this->data['listSet']['status'] = get_list_active_inactive();
        $this->data['listSet']['stock_flag'] = get_list_stock_flag();
        $this->data['listSet']['type'] = get_list_type_product();

        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.product-clinic.forms';
        $this->listView['create2'] = env('ADMIN_TEMPLATE').'.page.product-clinic.forms2';
        $this->listView['find-product-synapsa'] = env('ADMIN_TEMPLATE').'.page.product-clinic.forms3';
        $this->listView['create3'] = env('ADMIN_TEMPLATE').'.page.product-clinic.forms4';
        $this->listView['index'] = env('ADMIN_TEMPLATE').'.page.product-clinic.list';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.product-clinic.forms';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.product-clinic.forms';
    }

    public function create(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getData = Users::where('id', $adminId)->first();
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $data['thisLabel'] = __('general.product');
        $data['viewType'] = 'create';
        $data['formsTitle'] = __('general.title_create', ['field' => __('general.product') . ' ' . $getData->name]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;

        return view($this->listView[$data['viewType']], $data);
    }

    public function edit($id){
        $this->callPermission();

        $adminClinicId = session()->get('admin_clinic_id');

        $getData = $this->crud->show($id, [
            'id' => $id,
            'klinik_id' => $adminClinicId
        ]);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $product = Product::where('id',$id)->first();
        $getDescProduct = json_decode($product->desc, true);

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

        $data['thisLabel'] = __('general.product');
        $data['viewType'] = 'edit';
        $data['formsTitle'] = __('general.title_edit', ['field' => __('general.product') . ' ' . $getData->name]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['listProduct'] = $listProduct;
        $data['data'] = $getData;

        return view($this->listView[$data['viewType']], $data);
    }

    public function show($id){
        $this->callPermission();

        $adminClinicId = session()->get('admin_clinic_id');

        $getData = $this->crud->show($id, 'id', [
            'klinik_id' => [$adminClinicId, 0]
        ]);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $product = Product::where('id',$id)->first();
        $getDescProduct = json_decode($product->desc, true);

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

        $data['thisLabel'] = __('general.product');
        $data['viewType'] = 'show';
        $data['formsTitle'] = __('general.title_show', ['field' => __('general.product') . ' ' . $getData->name]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['listProduct'] = $listProduct;
        $data['data'] = $getData;

        return view($this->listView[$data['viewType']], $data);
    }

    public function store(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getData = Admin::where('id', $adminId)->first();
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $viewType = 'create';

        $getListCollectData = collectPassingData($this->passingData, $viewType);
        $validate = $this->setValidateData($getListCollectData, $viewType);
        if (count($validate) > 0) {
            $request = $this->request;

            if(in_array(null, $this->request->get('title'))) {
                unset($request['title']);
            }
            if(in_array(null, $this->request->get('desc'))) {
                unset($request['desc']);
            }

            $data = $this->validate($request, $validate);
        }
        else {
            $data = [];
            foreach ($getListCollectData as $key => $val) {
                $data[$key] = $this->request->get($key);
            }
        }

        $productStockFlag = $this->request->get('stock_flag');
        $dokument = $this->request->file('image_full');
        $desc = $this->request->get('desc');
        $title = $this->request->get('title');
        $productStock = $this->request->get('stock');

        if($productStockFlag != 1){
            $productStockFlag = 2;
        }
        else{
            $productStockFlag = 1;
        }

        $descProduct = [];

        $descProduct[]  =
        ['title' => $title,
         'desc' => $desc];

        $dokumentImage = '';
        if ($dokument) {
            if ($dokument->getError() != 1) {

                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/product';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }

        $product = new Product();
        $product->klinik_id = $getData->klinik_id;
        $product->product_category_id = $data['product_category_id'];
        $product->name = $data['name'];
        $product->price = clear_money_format($data['price']);
        $product->unit = $data['unit'];
        $product->stock = $productStock;
        $product->status = $data['status'];
        $product->type = $data['type'];
        $product->desc = json_encode($descProduct);
        $product->image = $dokumentImage;
        $product->stock_flag = $productStockFlag;
        $product->save();

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.show', $product->id);
        }
    }

    public function update($id){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getAdmin = Admin::where('id', $adminId)->first();
        if (!$getAdmin) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $getData = Product::where('id',$id)->first();
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $viewType = 'edit';

        $getListCollectData = collectPassingData($this->passingData, $viewType);
        $validate = $this->setValidateData($getListCollectData, $viewType);
        if (count($validate) > 0)
        {
            $request = $this->request;

            if(in_array(null, $this->request->get('title'))) {
                unset($request['title']);
            }
            if(in_array(null, $this->request->get('desc'))) {
                unset($request['desc']);
            }

            if($getData->parent_id > 0) {
                unset($request['stock']);
                unset($request['stock_flag']);
            }

            $data = $this->validate($request, $validate);
        }
        else {
            $data = [];
            foreach ($getListCollectData as $key => $val) {
                $data[$key] = $this->request->get($key);
            }
        }

        $productCategoryId = $data['product_category_id'];
        $productName = $data['name'];
        $productPrice = clear_money_format($data['price']);
        $productUnit = $data['unit'];
        $productStatus = $data['status'];
        $productType = $data['type'];
        $desc = $this->request->get('desc');
        $title = $this->request->get('title');
        $productStockFlag = $this->request->get('stock_flag');
        $productStock = $this->request->get('stock');

        if($productStockFlag != 1){
            $productStockFlag = 2;
        }
        else{
            $productStockFlag = 1;
        }

        if($getData->parent_id > 0) {
            $productParent = Product::where('id', $getData->parent_id)->first();
            if($productParent) {
                $productStock = $productParent->stock;
                $productStockFlag = $productParent->stock_flag;
            }
        }

        $descProduct = [];
        $descProduct[]  = [
            'title' => $title,
            'desc' => $desc
        ];

        $dokumentImage = $getData->image;

        $dokument = $this->request->file('image_full');
        if ($dokument) {
            if ($dokument->getError() != 1) {

                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/product';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }

        Product::where('id',$id)->update([
            'klinik_id' => $getAdmin->klinik_id,
            'product_category_id' => $productCategoryId,
            'name' => $productName,
            'price' => $productPrice,
            'unit' => $productUnit,
            'stock' => $productStock,
            'type' => $productType,
            'status' => $productStatus,
            'desc' => json_encode($descProduct),
            'image' => $dokumentImage,
            'stock_flag' => $productStockFlag,
        ]);

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.show', $id);
        }
    }

    public function dataTable()
    {
        $this->callPermission();

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();

        $dataTables = new DataTables();
        $builder = $this->model::query()->selectRaw('product.id, product.name as name, product_category.name as product_category_id, price, stock, product.status as status, type')
            ->leftJoin('product_category','product_category.id', '=', 'product.product_category_id')
            ->where('product.klinik_id', $getAdmin->klinik_id);

        if ($this->request->get('status') && $this->request->get('status') != 0) {
            $builder = $builder->where('appointment_lab.status', $this->request->get('status'));
        }
        if ($this->request->get('daterange')) {
            $getDateRange = $this->request->get('daterange');
            $dateSplit = explode(' | ', $getDateRange);
            $dateStart = date('Y-m-d 00:00:00', strtotime($dateSplit[0]));
            $dateEnd = isset($dateSplit[1]) ? date('Y-m-d 23:59:59', strtotime($dateSplit[1])) : date('Y-m-d 23:59:59', strtotime($dateSplit[0]));

            $builder = $builder->whereBetween('appointment_lab.created_at', [$dateStart, $dateEnd]);
        }

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
            } else if (in_array($list['type'], ['money'])) {
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return number_format($query->$fieldName, 0);
                });
            } else if (in_array($list['type'], ['image'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<img src="' . asset($list['path'] . $query->$fieldName) . '" class="img-responsive max-image-preview"/>';
                });
            } else if (in_array($list['type'], ['image_preview'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<img src="' . $query->$fieldName . '" class="img-responsive max-image-preview"/>';
                });
            } else if (in_array($list['type'], ['code'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<pre>' . json_encode(json_decode($query->$fieldName, true), JSON_PRETTY_PRINT) . '"</pre>';
                });
            } else if (in_array($list['type'], ['texteditor'])) {
                $listRaw[] = $fieldName;
            }
        }
        return $dataTables
            ->rawColumns($listRaw)
            ->make(true);
    }

    public function create2(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getData = Users::where('id', $adminId)->first();
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        if($this->request->get('download_example_import')) {
            $getLogic = new SynapsaLogic();
            $getLogic->downloadExampleImportProduct();
        }

        $data = $this->data;

        $data['thisLabel'] = __('general.product');
        $data['viewType'] = 'create';
        $data['formsTitle'] = __('general.title_create', ['field' => __('general.product') . ' ' . $getData->name]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;

        return view($this->listView['create2'], $data);
    }

    public function store2(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getAdmin = Admin::where('id', $adminId)->first();
        if (!$getAdmin) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $this->validate($this->request, [
            'import_product' => 'required',
        ]);

        //A-N
        //A = Nomor
        //B = Kategori Produk
        //C = SKU
        //D = Nama Produk
        //E = Harga Produk
        //F = Unit Produk
        //G = Stock Produk
        //H = Stock Flag (1 Unlimited, 2 Limited)
        //I = Title(1)
        //J = Desc(1)
        //K = Title(2)
        //L = Desc(2)
        //M = Title(3)
        //N = Desc(3)
        //Start From Row 6

        $getFile = $this->request->file('import_product');

        if($getFile) {
//            $destinationPath = 'synapsaapps/product/example_import';
//
//            $getUrl = Storage::put($destinationPath, $getFile);
//
//            die(env('OSS_URL') . '/' . $getUrl);

            try {
                $getFileName = $getFile->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                if (in_array(strtolower($ext), ['xlsx', 'xls'])) {
                    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($getFile);
                    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                    $data = $reader->load($getFile);

                    if ($data) {
                        $spreadsheet = $data->getActiveSheet();
                        foreach ($spreadsheet->getRowIterator() as $key => $row) {
                            if($key >= 6) {
                                $kategoriProduk = $spreadsheet->getCell("B" . $key)->getValue();
                                $sku = $spreadsheet->getCell("C" . $key)->getValue();
                                $namaProduk = $spreadsheet->getCell("D" . $key)->getValue();
                                $hargaProduk = $spreadsheet->getCell("E" . $key)->getValue();
                                $unitProduk = $spreadsheet->getCell("F" . $key)->getValue();
                                $stockProduk = $spreadsheet->getCell("G" . $key)->getValue();
                                $stockFlag = strtolower(str_replace(' ', '', $spreadsheet->getCell("H" . $key)->getValue()));
                                $title1 = $spreadsheet->getCell("I" . $key)->getValue();
                                $desc1 = $spreadsheet->getCell("J" . $key)->getValue();
                                $title2 = $spreadsheet->getCell("K" . $key)->getValue();
                                $desc2 = $spreadsheet->getCell("L" . $key)->getValue();
                                $title3 = $spreadsheet->getCell("M" . $key)->getValue();
                                $desc3 = $spreadsheet->getCell("N" . $key)->getValue();

                                $kategoriCheck = ProductCategory::where('name', $kategoriProduk)->first();
                                if($kategoriCheck) {
                                    $kategoriProduk = $kategoriCheck->id;
                                }
                                else {
                                    if(strlen($kategoriProduk) > 0) {
                                        $saveCategory = [
                                            'name' => $kategoriProduk,
                                            'status' => 80
                                        ];

                                        $productCategory = ProductCategory::create($saveCategory);
                                        $kategoriProduk = $productCategory->id;
                                    }
                                }

                                $flag = strtolower(str_replace(' ', '', $stockFlag));
                                if($flag == 'unlimited') {
                                    $stockFlag = 1;
                                }
                                else if($flag = 'limited') {
                                    $stockFlag = 2;
                                }
                                else {
                                    $stockFlag = 0;
                                }

                                $descProduct = [];
                                if(strlen($title1) > 0) {
                                    if(strlen($title2) > 0 && strlen($title3) > 0) {
                                        $descProduct[] = [
                                            'title' => [$title1, $title2, $title3],
                                            'desc' => [$desc1, $desc2, $desc3],
                                        ];
                                    }
                                    else if(strlen($title2) > 0 && strlen($title3) <= 0) {
                                        $descProduct[] = [
                                            'title' => [$title1, $title2],
                                            'desc' => [$desc1, $desc2],
                                        ];
                                    }
                                    else if(strlen($title3) > 0 && strlen($title2) <= 0) {
                                        $descProduct[] = [
                                            'title' => [$title1, $title3],
                                            'desc' => [$desc1, $desc3],
                                        ];
                                    }
                                    else {
                                        $descProduct[] = [
                                            'title' => [$title1],
                                            'desc' => [$desc1],
                                        ];
                                    }
                                }

                                $saveData = [
                                    'product_category_id' => $kategoriProduk,
                                    'klinik_id' => $getAdmin->klinik_id,
                                    'sku' => $sku,
                                    'name' => $namaProduk,
                                    'price' => $hargaProduk,
                                    'unit' => $unitProduk,
                                    'stock' => $stockProduk,
                                    'stock_flag' => $stockFlag,
                                    'desc' => json_encode($descProduct),
                                    'status' => 80,
                                ];

                                if(strlen($namaProduk) > 0) {
                                    Product::create($saveData);
                                }
                            }
                        }
                    }
                }
            }
            catch(\Exception $e) {
                session()->flash('message', __('general.failed_import_product'));
                session()->flash('message_alert', 1);
                return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
            }
        }

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }
    }

    public function getProductSynapsa(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getAdmin = Admin::where('id', $adminId)->first();
        if (!$getAdmin) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $getCategory = ProductCategory::where('status', 80)->get();

        $getData = $this->data;

        $data['thisLabel'] = __('general.product');
        $data['viewType'] = 'create';
        $data['formsTitle'] = __('general.search_product_from_synapsa');
        //$data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;
        $data['category'] = $getCategory;

        return view($this->listView['find-product-synapsa'], $data);
    }

    public function create3(){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getAdmin = Admin::where('id', $adminId)->first();
        if (!$getAdmin) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $productId = intval($this->request->get('id'));

        $getData = $this->crud->show($productId);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        if($productId){
            $product = Product::where('id', 'LIKE', strip_tags($productId))->first();
        }
        //dd($product);
        $getDescProduct = json_decode($product->desc, true);

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

        $data['thisLabel'] = __('general.product');
        $data['viewType'] = 'edit';
        $data['formsTitle'] = __('general.title_create', ['field' => __('general.product')]);
        $data['passing'] = collectPassingData($this->passingDataAddFromSynapsa, $data['viewType']);
        $data['data'] = $getData;
        $data['listProduct'] = $listProduct;

        return view($this->listView['create3'], $data);

    }

    public function store3($id){
        $this->callPermission();

        $adminId = session()->get('admin_id');
        $getAdmin = Admin::where('id', $adminId)->first();
        if (!$getAdmin) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $viewType = 'edit';
        $getProductSynapsa = Product::where('id',$id)->first();

        $getListCollectData = collectPassingData($this->passingDataAddFromSynapsa, $viewType);
        $validate = $this->setValidateData($getListCollectData, $viewType);
        if (count($validate) > 0)
        {
            $data = $this->validate($this->request, $validate);
        }
        else {
            $data = [];
            foreach ($getListCollectData as $key => $val) {
                $data[$key] = $this->request->get($key);
            }
        }

        $productCategoryId = $data['product_category_id'];
        $productName = $data['name'];
        $productPrice = clear_money_format($data['price']);
        $productUnit = $data['unit'];
        $productStatus = $data['status'];
        $desc = $this->request->get('desc');
        $title = $this->request->get('title');

        $descProduct = [];

        $descProduct[] = [
            'title' => $title,
            'desc' => $desc
        ];

        $dokumentImage = $getProductSynapsa->image;

        $dokument = $this->request->file('image_full');
        if ($dokument) {
            if ($dokument->getError() != 1) {

                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/product';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }
        $product = new Product();
        $product->parent_id = $id;
        $product->klinik_id = $getAdmin->klinik_id;
        $product->product_category_id = $productCategoryId;
        $product->name = $productName;
        $product->price = $productPrice;
        $product->unit = $productUnit;
        $product->status = $productStatus;
        $product->desc = json_encode($descProduct);
        $product->image = $dokumentImage;
        $product->stock = $getProductSynapsa->stock;
        $product->stock_flag = $getProductSynapsa->stock_flag;

        $product->save();

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.show', $product->id);
        }
    }

}
