<?php

namespace App\Codes\Logic;

class _CRUD
{
    protected $model;

    public function __construct($model)
    {
        $this->model = 'App\Codes\Models\\' . $model;
    }

    public function all()
    {
        return $this->model::get();
    }

    public function store($data)
    {
        $saveData = new $this->model();
        foreach ($data as $key => $value) {
            $saveData->$key = $value;
        }
        $saveData->save();

        return $saveData;
    }

    public function show($id, $key = 'id', $condition = [])
    {
        $model = $this->model::where($key, $id);
        if ($condition) {
            foreach ($condition as $keyCondition => $valCondition) {
                if (is_array($valCondition)) {
                    $model = $model->whereIn($keyCondition, $valCondition);
                }
                else {
                    $model = $model->where($keyCondition, '=', $valCondition);
                }
            }
        }
        return $model->first();
    }

    public function update($data, $id, $key = 'id')
    {
        $saveData = $this->model::where($key, $id)->first();
        foreach ($data as $key => $value) {
            $saveData->$key = $value;
        }
        $saveData->save();

        return $saveData;
    }

    public function destroy($id, $key = 'id')
    {
        $getData = $this->model::where($key, $id)->first();
        $getData->delete();
    }

    public function saveImageFile($listImage, $request, $destinationPath)
    {
        $data = [];

        foreach ($listImage as $imageKey => $list) {

            try {
                $image = $request->file($imageKey);
                if ($image && $image->getError() == 1) {
                    if ($list === true) {
                        if ($image->getSize() <= 0) {
                            $message = __('general.error_max_file_', ['bytes' => '25M', 'files' => 'Image']);
                        } else {
                            $message = __('general.error_upload_file_', ['files' => 'Image']);
                        }
                        return [
                            'success' => 0,
                            'message' => [
                                $imageKey => $message
                            ]
                        ];
                    }
                }
                if ($image) {
                    $getFileName = $image->getClientOriginalName();
                    $ext = explode('.', $getFileName);
                    $ext = end($ext);
                    $setFileName = md5($imageKey . strtotime('now') . rand(0, 100)) . '.' . $ext;

                    $image->move($destinationPath, $setFileName);
                    $data[$imageKey] = $setFileName;

                }
            }
            catch (\Exception $e) {
                return [
                    'success' => 0,
                    'message' => [
                        $imageKey => 'Error'
                    ]
                ];
            }

        }

        return [
            'success' => 1,
            'data' => $data
        ];

    }

    public function saveImageBase64($listImage, $request, $destinationPath)
    {
        $data = [];

        foreach ($listImage as $imageKey => $list) {
            try {
                $image = base64_to_jpeg($request->get($imageKey));
                $setFileName = md5($imageKey.strtotime('now').rand(0, 100)).'.jpg';
                file_put_contents($destinationPath.$setFileName, $image);
                $data[$imageKey] = $setFileName;
            }
            catch (\Exception $e) {
                return [
                    'success' => 0,
                    'message' => [
                        $imageKey => 'Error'
                    ]
                ];
            }
        }

        return [
            'success' => 1,
            'data' => $data
        ];

    }

}
