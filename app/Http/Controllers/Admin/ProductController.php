<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;

class ProductController extends _CrudController
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
            $request, 'general.product', 'product', 'V1\Product', 'product',
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
        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.product.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.product.forms';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.product.forms';
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
        $getData = Users::where('id', $adminId)->first();
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
        $getUser = Users::where('id', $adminId)->first();
        if (!$getUser) {
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
}
