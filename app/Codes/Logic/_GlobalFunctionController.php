<?php

namespace App\Codes\Logic;

use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;

class _GlobalFunctionController extends Controller
{
    protected $data;
    protected $permission;
    protected $module;
    protected $passingData;

    protected function setValidateData($getListCollectData, $formsType, $id = null)
    {
        $validate = [];
        foreach ($getListCollectData as $key => $setData) {
            if (strlen($setData['validate'][$formsType]) > 0) {
                $getListValidate = explode('|', $setData['validate'][$formsType]);
                $getValidate = [];
                foreach ($getListValidate as $listValidate) {
                    if(strpos($listValidate, 'unique') === 0) {
                        $setValidate = $listValidate;
                        if ($id != null) {
                            $setValidate .= ','.$id;
                        }
                        $getValidate[] = $setValidate;
                    }
                    else {
                        $getValidate[] = $listValidate;
                    }
                }

                $validate[$key] = implode('|', $getValidate);

            }
            else {

                $validate[$key] = '';

            }
        }

        return $validate;
    }

    protected function getCollectedData($getListCollectData, $formsType, $data, $getData = null)
    {
        foreach ($getListCollectData as $key => $setData) {
            if (in_array($setData['type'], ['password'])) {
                if (strlen($data[$key]) > 0) {
                    $data[$key] = bcrypt($data[$key]);
                }
                else {
                    unset($data[$key]);
                }
            }
            else if (in_array($setData['type'], ['money'])) {
                $data[$key] = clear_money_format($data[$key]);
            }
            else if (in_array($setData['type'], ['checkbox'])) {
                if (isset($data[$key])) {
                    $data[$key] = 1;
                }
                else {
                    $data[$key] = 0;
                }
            }
            else if (in_array($setData['type'], ['tagging'])) {
                $data[$key] = implode(',', $data[$key]);
            }
        }

        foreach ($getListCollectData as $imageKey => $setData) {
            if (in_array($setData['type'], ['image'])) {
                unset($data[$imageKey]);
                $image = $this->request->file($imageKey);
                if ($image) {
                    if ($image->getError() != 1) {

                        $getFileName = $image->getClientOriginalName();
                        $ext = explode('.', $getFileName);
                        $ext = end($ext);
                        $setFileName = md5(strtotime('now').rand(0, 100)).'.'.$ext;
                        $destinationPath = $setData['path'];
                        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {
                            if ($getData && strlen($getData->$imageKey) > 0 && is_file($destinationPath.$getData->$imageKey)) {
                                unlink($destinationPath.$getData->$imageKey);
                            }

                            $image->move($destinationPath, $setFileName);

                            $data[$imageKey] = $setFileName;
                        }
                    }
                }
            }
            else if (in_array($setData['type'], ['sound', 'video', 'file'])) {
                unset($data[$imageKey]);
                $image = $this->request->file($imageKey);
                if ($image) {
                    if ($image->getError() != 1) {
                        $getFileName = $image->getClientOriginalName();
                        $ext = explode('.', $getFileName);
                        $ext = end($ext);
                        $setFileName = md5(strtotime('now').rand(0, 100)).'.'.$ext;
                        $destinationPath = $setData['path'];

                        if ($getData && strlen($getData->$imageKey) > 0 && is_file($destinationPath.$getData->$imageKey)) {
                            unlink($destinationPath.$getData->$imageKey);
                        }

                        $image->move($destinationPath, $setFileName);
                        $data[$imageKey] = $setFileName;
                    }
                }
            }
            else if (in_array($setData['type'], ['image_many'])) {
                unset($data[$imageKey]);
                $tempImage = [];
                if ($this->request->file($imageKey)) {
                    foreach ($this->request->file($imageKey) as $image) {
                        if ($image) {
                            if ($image->getError() != 1) {

                                $getFileName = $image->getClientOriginalName();
                                $ext = explode('.', $getFileName);
                                $ext = end($ext);
                                $setFileName = md5(strtotime('now').rand(0, 100)).'.'.$ext;
                                $destinationPath = $setData['path'];
                                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {
                                    if ($getData && strlen($getData->$imageKey) > 0 && is_file($destinationPath.$getData->$imageKey)) {
                                        unlink($destinationPath.$getData->$imageKey);
                                    }

                                    $image->move($destinationPath, $setFileName);

                                    $tempImage[] = $setFileName;
                                }
                            }
                        }
                    }

                    $data[$imageKey] = json_encode($tempImage);

                }

            }
        }

        return $data;
    }

    protected function callPermission() {
        $this->permission = getDetailPermission($this->module);

//        $this->permission = array(
//            'create' => true,
//            'edit' => true,
//            'show' => true,
//            'list' => true,
//            'destroy' => true
//        );

        $this->data['permission'] = $this->permission;
    }

}
