<?php

namespace App\Codes\Logic;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\ProductCategory;
use App\Codes\Models\V1\Service;
use Illuminate\Support\Facades\Cache;

class LabLogic
{
    public function __construct()
    {
    }

    /**
     * @param null $serviceId
     * @param null $subServiceId
     * @return array
     */
    public function getListService($serviceId = null, $subServiceId = null): array
    {
        $setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });

        $getServiceLab = isset($setting['service-lab']) ? json_decode($setting['service-lab'], true) : [];
        if (count($getServiceLab) > 0) {
            $service = Service::whereIn('id', $getServiceLab)->where('status', '=', 80)->orderBy('orders', 'ASC')->get();
        }
        else {
            $service = Service::where('status', '=', 80)->orderBy('orders', 'ASC')->get();
        }

        $result = [];
        $subService = [];
        $firstServiceId = 0;
        $firstServiceName = '';
        $firstServiceType = 0;
        $setServiceId = 0;
        $setServiceName = '';
        $setServiceType = 0;

        $setSubServiceId = 0;
        $setSubServiceName = '';

        foreach ($service as $index => $list) {
            $active = 0;
            if ($index == 0) {
                $firstServiceId = $list->id;
                $firstServiceName = $list->name;
                $firstServiceType = $list->type;
            }

            if ($list->id == $serviceId) {
                $active = 1;
                $setServiceId = $list->id;
                $setServiceName = $list->name;
                $setServiceType = $list->type;
            }

            $result[] = [
                'id' => $list->id,
                'name' => $list->name,
                'type' => $list->type,
                'type_nice' => $list->type_nice,
                'active' => $active
            ];

        }

        if ($setServiceId <= 0) {
            $setServiceId = $firstServiceId;
            $setServiceName = $firstServiceName;
            $setServiceType = $firstServiceType;
        }

        if ($setServiceType == 1) {
            $subService = get_list_sub_service();

            $subServiceId = intval($subServiceId);
            $getList = get_list_sub_service2();
            if (isset($getList[$subServiceId])) {
                $setSubServiceId = $subServiceId;
                $setSubServiceName = $getList[$subServiceId];
            }
        }

        return [
            'data' => $result,
            'sub_service' => $subService,
            'getServiceId' => $setServiceId,
            'getServiceName' => $setServiceName,
            'getSubServiceId' => $setSubServiceId,
            'getSubServiceName' => $setSubServiceName
        ];

    }

    /**
     * @param $limit
     * @param null $clinicId
     * @param null $serviceId
     * @param null $search
     * @param int $priority
     * @return array
     */
    public function labGet($limit, $serviceId = null, $clinicId = null, $search = null, int $priority = 0): array
    {
        if ($serviceId) {
            $getData = Lab::selectRaw('lab.id ,lab.name, lab_service.service_id, lab_service.price, lab.image, klinik_id')
                ->join('lab_service', 'lab_service.lab_id','=','lab.id')
                ->where('lab_service.service_id','=', $serviceId);
        }
        else {
            $getData = Lab::selectRaw('lab.id ,lab.name, 0 AS service_id, 0 AS price, lab.image, klinik_id');
        }

        if ($clinicId) {
            $getData = $getData->where('lab.klinik_id', '=', $clinicId);
        }

        if (strlen($search) > 0) {
            $search = strip_tags($search);
            $getData = $getData->where('lab.name', 'LIKE', $search)->orWhere('lab.desc_lab', 'LIKE', $search);
        }

        if ($priority == 1) {
            $getData = $getData->where('priority', 1);
        }

        $getData = $getData->where('lab.parent_id', '=', 0)->orderBy('lab.name','ASC')->paginate($limit);

        return [
            'lab' => $getData
        ];

    }

    /**
     * @param $labId
     * @param $serviceId
     * @param null $clinicId
     * @return array
     */
    public function labInfo($labId, $serviceId, $clinicId = null): array
    {
        $getData = Lab::selectRaw('lab.id, lab.name, lab.image, lab.desc_lab, lab.desc_benefit,
            lab.desc_preparation, lab.recommended_for, lab_service.service_id, lab_service.price')
            ->join('lab_service', 'lab_service.lab_id','=','lab.id')
            ->where('lab_service.service_id','=', $serviceId)
            ->where('lab.id','=', $labId);

        if ($clinicId) {
            $getData = $getData->where('lab.klinik_id', '=', $clinicId);
        }

        $getData = $getData->first();
        $getDataChild = [];
        $haveChild = 0;
        if ($getData && $getData->parent == 0) {
            $haveChild = 1;
            $getDataChild = Lab::selectRaw('lab.id, lab.parent_id, lab.name, lab.image, lab.desc_lab, lab.desc_benefit,
                lab.desc_preparation, lab.recommended_for, lab_service.service_id, lab_service.price')
                ->join('lab_service', 'lab_service.lab_id','=','lab.id')
                ->where('lab.parent_id','=', $getData->id)
                ->where('lab_service.service_id','=', $serviceId)->get();
        }

        return [
            'lab' => $getData,
            'have_child' => $haveChild,
            'child_lab' => $getDataChild
        ];

    }

}
