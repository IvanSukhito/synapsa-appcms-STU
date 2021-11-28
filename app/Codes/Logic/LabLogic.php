<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\Lab;

class LabLogic
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
    public function labGet($clinicId, $limit, $categoryId = null, $search = null): array
    {
        $getData = Lab::selectRaw('id, name, image, unit, price, stock, stock_flag, type, status')
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
            'lab' => $getData,
            'category' => $category
        ];

    }

    /**
     * @param $clinicId
     * @param $id
     * @return mixed
     */
    public function labInfo($clinicId, $id)
    {
        return Lab::where('klinik_id', '=', $clinicId)->where('id', '=', $id)->where('status', '=', 80)->first();
    }

}
