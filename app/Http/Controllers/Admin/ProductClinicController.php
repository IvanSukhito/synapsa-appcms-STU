<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Logic\SynapsaLogic;
use App\Codes\Models\Admin;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ProductClinicController extends _CrudController
{
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
                    'edit' => ''
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
            ],
            'stock_flag' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
                'list' => 0,
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

        parent::__construct(
            $request, 'general.product_clinic', 'product-clinic', 'V1\Product', 'product-clinic',
            $passingData
        );

        $getCategory = ProductCategory::where('status', 80)->pluck('name', 'id')->toArray();
        if($getCategory) {
            foreach($getCategory as $key => $value) {
                $listCategory[$key] = $value;
            }
        }

        $this->data['listSet']['product_category_id'] = $listCategory;
        $this->data['listSet']['status'] = get_list_active_inactive();
        $this->data['listSet']['stock_flag'] = get_list_stock_flag();
        //$this->listView['index'] = env('ADMIN_TEMPLATE').'.page.product.list';
        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.product-clinic.forms';
        $this->listView['create2'] = env('ADMIN_TEMPLATE').'.page.product-clinic.forms2';
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

        $adminId = session()->get('admin_id');
        $getUsers = Users::where('id', $adminId)->first();
        if (!$getUsers) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }


        $getData = $this->crud->show($id);
        //dd($getData);
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

        $adminId = session()->get('admin_id');
        $getUsers = Users::where('id', $adminId)->first();
        if (!$getUsers) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }


        $getData = $this->crud->show($id);
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

        //dd($listProduct);

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

        $productCategoryId = $this->request->get('product_category_id');
        $productName = $this->request->get('name');
        $productPrice = clear_money_format($this->request->get('price'));
        $productUnit = $this->request->get('unit');
        $productStock = $this->request->get('stock');
        $productStockFlag = $this->request->get('stock_flag');
        $productStatus = $this->request->get('status');
        $dokument = $this->request->file('image_full');
        $desc = $this->request->get('desc');
        $title = $this->request->get('title');

        $descProduct = [];

        $descProduct[]  =
        ['title' => $title,
         'desc' => $desc];
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
        $product->product_category_id = $productCategoryId;
        $product->name = $productName;
        $product->price = $productPrice;
        $product->unit = $productUnit;
        $product->stock = $productStock;
        $product->status = $productStatus;
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
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
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
            $data = $this->validate($this->request, $validate);
        }
        else {
            $data = [];
            foreach ($getListCollectData as $key => $val) {
                $data[$key] = $this->request->get($key);
            }
        }


        $productCategoryId = $this->request->get('product_category_id');
        $productName = $this->request->get('name');
        $productPrice = clear_money_format($this->request->get('price'));
        $productUnit = $this->request->get('unit');
        $productStock = $this->request->get('stock');
        $productStockFlag = $this->request->get('stock_flag');
        $productStatus = $this->request->get('status');
        $productInformation = $this->request->get('information');
        $productIndication = $this->request->get('indication');
        $productDosis = $this->request->get('dosis');
        $dokument = $this->request->file('image_full');
        $title = $this->request->get('title');
        $desc = $this->request->get('desc');
        $descProduct = [];
        $descProduct[]  =
        [   'title' => $title,
            'desc' => $desc   ];




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
        }else{

            $dokumentImage= $getData->image;

        }

        $product = Product::where('id',$id)->update([
            'klinik_id' => $getAdmin->klinik_id,
            'product_category_id' => $productCategoryId,
            'name' => $productName,
            'price' => $productPrice,
            'unit' => $productUnit,
            'stock' => $productStock,
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
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }
    }

    public function dataTable()
    {
        $this->callPermission();

        $adminId = session()->get('admin_id');

        $getAdmin = Admin::where('id', $adminId)->first();

        $dataTables = new DataTables();
        $builder = $this->model::query()->selectRaw('product.id, product.name as name, product_category.name as product_category_id, price, stock, product.status as status')
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
                                    $saveCategory = [
                                        'name' => $kategoriProduk,
                                        'status' => 80
                                    ];

                                    $productCategory = ProductCategory::create($saveCategory);
                                    $kategoriProduk = $productCategory->id;
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

}
