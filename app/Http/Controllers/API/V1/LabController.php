<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Lab;
use App\Codes\Models\V1\LabCart;
use App\Codes\Models\V1\Service;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class LabController extends Controller
{
    protected $request;
    protected $setting;
    protected $limit;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->limit = 5;
        $this->setting = Cache::remember('setting', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
    }

    public function getLab(){

        $user = $this->request->attributes->get('_user');

        $serviceId = intval($this->request->get('service_id'));

        $s = strip_tags($this->request->get('s'));
        $getLimit = $this->request->get('limit');
        if ($getLimit <= 0) {
            $getLimit = $this->limit;
        }

        $service = Service::orderBy('orders', 'ASC')->get();

        $getInterestService = $serviceId > 0 ? $serviceId : $user->interest_service_id;
        $getServiceId = 0;
        $firstService = 0;
        $tempService = [];
        foreach ($service as $index => $list) {
            $temp[] = [
                'id' => $list->id,
                'name' => $list->name,
                'active' => 0
            ];

            if ($index == 0) {
                $firstService = $list->id;
            }
            if ($getInterestService == $list->id) {
                $temp['active'] = 1;
                $getServiceId = $list->id;
            }

            $tempService[] = $temp;

        }

        $service = $tempService;

        if ($getServiceId == 0) {
            if ($firstService > 0) {
                $service[0]['active'] = 1;
            }
            $getServiceId = $firstService;
        }

        $data = Lab::selectRaw('lab.id ,lab.name, lab_service.price, lab.image')
        ->join('lab_service', 'lab_service.lab_id','=','lab.id')
        ->where('lab_service.service_id','=', $getServiceId)
        ->where('lab.parent_id', '=', 0);

        if (strlen($s) > 0) {
            $data = $data->where('name', 'LIKE', "%$s%");
        }

        $data = $data->orderBy('name', 'ASC')->paginate($getLimit);

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'lab' => $data,
                'service' => $service
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getLabDetail($id){

        $user = $this->request->attributes->get('_user');

        $serviceId = intval($this->request->get('service_id'));

        $service = Service::orderBy('orders', 'ASC')->get();

        $getInterestService = $serviceId > 0 ? $serviceId : $user->interest_service_id;
        $getServiceId = 0;
        $firstService = 0;
        foreach ($service as $index => $list) {
            if ($index == 0) {
                $firstService = $list->id;
            }
            if ($getInterestService == $list->id) {
                $getServiceId = $list->id;
            }
        }

        if ($getServiceId == 0) {
            $getServiceId = $firstService;
        }

        $data = Lab::selectRaw('lab.parent_id, lab.name, lab.image, lab.desc_lab,lab.desc_benefit,
            lab.desc_preparation, lab.recommended_for, lab_service.price')
            ->join('lab_service', 'lab_service.lab_id','=','lab.id')
            ->where('lab_service.service_id','=', $getServiceId)
            ->where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

        if ($data->parent_id == 0) {
            $dataLabTerkait = Lab::selectRaw('lab.id ,lab.name, lab_service.price, lab.image')
                ->join('lab_service', 'lab_service.lab_id','=','lab.id', 'LEFT')
                ->where('lab_service.service_id','=', $getServiceId)
                ->where('lab.parent_id', '=', $id)->get();

            $getData = [
                'lab' => $data,
                'lab_terkait' => $dataLabTerkait
            ];
        }
        else {
            $getData = [
                'lab' => $data
            ];
        }

        return response()->json([
            'success' => 1,
            'data' => $getData,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getCart(){

        $user = $this->request->attributes->get('_user');

        $getLabCart = LabCart::where('user_id', $user->id)->first();
        $getService = [];
        $getData = [];
        $total = 0;
        if ($getLabCart) {
            $getService = Service::where('id', $getLabCart->service_id)->first();
            $getData = Lab::selectRaw('lab.id ,lab.name, lab_service.price, lab.image')
                ->join('lab_service', 'lab_service.lab_id','=','lab.id')
                ->join('lab_cart', 'lab_cart.lab_id','=','lab.id')
                ->where('lab_service.service_id', $getLabCart->service_id)
                ->where('user_id', $user->id)->get();
            foreach ($getData as $list) {
                $total += $list->price;
            }
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'cart' => $getData,
                'service' => $getService,
                'total' => $total,
                'total_nice' => number_format($total, 0, '.', '.')
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function storeCart()
    {
        $user = $this->request->attributes->get('_user');

        $validator = Validator::make($this->request->all(), [
            'lab_id' => 'required|numeric',
            'service_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->messages()->all(),
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        $getLabId = $this->request->get('lab_id');
        $getServiceId = $this->request->get('service_id');

        $getLabCart = LabCart::where('user_id', $user->id)->first();
        if ($getLabCart && $getLabCart->service_id != $getServiceId) {
            return response()->json([
                'success' => 0,
                'message' => ['Test Lab menggunakan service yang berbeda'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 422);
        }

        LabCart::firstOrCreate([
            'user_id' => $user->id,
            'lab_id' => $getLabId,
            'service_id' => $getServiceId,
        ]);

        $total = 0;
        $getService = Service::where('id', $getServiceId)->first();
        $getData = Lab::selectRaw('lab.id ,lab.name, lab_service.price, lab.image')
            ->join('lab_service', 'lab_service.lab_id','=','lab.id')
            ->join('lab_cart', 'lab_cart.lab_id','=','lab.id')
            ->where('lab_service.service_id', $getServiceId)
            ->where('user_id', $user->id)->get();
        foreach ($getData as $list) {
            $total += $list->price;
        }

        return response()->json([
            'success' => 1,
            'data' => [
                'cart' => $getData,
                'service' => $getService,
                'total' => $total,
                'total_nice' => number_format($total, 0, '.', '.')
            ],
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function deleteCart($id)
    {
        $user = $this->request->attributes->get('_user');

        $getData = LabCart::where('id', $id)->first();
        if (!$getData) {
            return response()->json([
                'success' => 0,
                'message' => ['Lab Not Found'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }

    }

}
