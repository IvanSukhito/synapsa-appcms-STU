<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\ProductCategory;
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
                'lang' => 'general.product_category_id',
                'type' => 'select',
            ],
            'sku' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'name' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ]
            ],
            'image' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'image',
            ],
            'price' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ]
            ],
            'unit' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ]
            ],
            'desc' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'textarea',
            ],
            'stock' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ]
            ],
            'stock_flag' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ]
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

        $getCategory = ProductCategory::get()->pluck('name', 'id')->toArray();
        $listCategory = [0 => 'Kosong'];
        if($getCategory) {
            foreach($getCategory as $key => $value) {
                $listCategory[$key] = $value;
            }
        }

        $this->data['listSet']['product_category_id'] = $listCategory;
    }
}
