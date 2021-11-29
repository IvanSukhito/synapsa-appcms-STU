<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\ProductCategory;

class ProductLogic
{
    public function __construct()
    {
    }

    /**
     * @param $clinicId
     * @param $limit
     * @param null $categoryId
     * @param null $search
     * @return array
     */
    public function productGet($clinicId, $limit, $categoryId = null, $search = null): array
    {
        $getData = Product::selectRaw('id, name, image, unit, price, stock, stock_flag, type, status')
            ->where('klinik_id', '=', $clinicId)->where('status', '=', 80);
        if (strlen($search) > 0) {
            $search = strip_tags($search);
            $getData = $getData->where('name', 'LIKE', $search)->orWhere('desc', 'LIKE', $search);
        }
        if (intval($categoryId) > 0) {
            $getData = $getData->where('product_category_id', '=', intval($categoryId));
        }

        $getData = $getData->orderBy('id','DESC')->paginate($limit);

        $category = ProductCategory::where('status', '=', 80)->get();

        return [
            'product' => $getData,
            'category' => $category
        ];

    }

    /**
     * @param $clinicId
     * @param $id
     * @return mixed
     */
    public function productInfo($clinicId, $id)
    {
        return Product::where('klinik_id', '=', $clinicId)->where('id', '=', $id)->where('status', '=', 80)->first();
    }

    /**
     * @param $products
     */
    public function reduceStock($products)
    {
        $productIds = [];
        foreach ($products as $product => $qty) {
            $productIds[] = $product;
        }

        $updateParents = [];
        $getProducts = Product::whereIn('id', $productIds)->get();
        foreach ($getProducts as $getProduct) {
            $getQty = isset($products[$getProduct->id]) ? intval($products[$getProduct->id]) : 0;

            if ($getProduct->parent_id > 0) {
                $updateParents[$getProduct->parent_id] = $getQty;
            }

            $getProduct->stock -= $getQty;
            $getProduct->save();

        }

        if (count($updateParents) > 0) {
            $this->reduceStock($updateParents);
        }

    }

}
