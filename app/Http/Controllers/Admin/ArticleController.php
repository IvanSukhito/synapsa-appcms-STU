<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\ArticleCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Codes\Models\V1\Article;

class ArticleController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
            ],
            'klinik_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
            ],
            'article_category_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'select2',
            ],
            'title' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'preview' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'texteditor',
                'list' => 0,
            ],
            'thumbnail_img_full' => [
                'validate' => [
                    'create' => 'required',
                ],
                'type' => 'image',
                'lang' => 'general.thumbnail_img'
            ],
            'image_full' => [
                'validate' => [
                    'create' => 'required',
                ],
                'type' => 'image',
                'list' => 0,
            ],
            'content' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'texteditor',
                'list' => 0,
            ],
            'publish_status' => [
                'validate' => [
                    'create' => '',
                    'edit' => ''
                ],
                'list' => 0,
                'type' => 'checkbox',
                'lang' => 'publish',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'custom' => ',orderable:false'
            ]
        ];

        parent::__construct(
            $request, 'general.article', 'article', 'V1\Article', 'article',
            $passingData
        );


        $getArticleCategory = ArticleCategory::where('status', 80)->pluck('name', 'id')->toArray();

        if($getArticleCategory) {
            foreach($getArticleCategory as $key => $value) {
                $listArticleCategory[$key] = $value;
            }
        }


        $this->data['listSet']['article_category_id'] = $listArticleCategory;
       // $this->data['listSet']['publish_status'] = get_list_status_article();
    }


    public function store()
    {
        $this->callPermission();

        $viewType = 'create';

        if($this->request->get('klinik_id') <= 0) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'klinik_id' => __('general.data_empty')
                ]);
        }

        $getListCollectData = collectPassingData($this->passingData, $viewType);

        unset($getListCollectData['thumbnail_img_full']);
        unset($getListCollectData['image_full']);

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

        $dokument = $this->request->file('image_full');
        if ($dokument) {
            if ($dokument->getError() != 1) {

                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/article';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }

        $dokumentThumbnail = $this->request->file('thumbnail_img_full');
        if ($dokumentThumbnail) {
            if ($dokumentThumbnail->getError() != 1) {

                $getFileName = $dokumentThumbnail->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/article';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentThumbnailImage = Storage::putFile($destinationPath, $dokumentThumbnail);
                }

            }
        }

        $statusPublish = $this->request->get('publish_status');
        //dd($statusPublish);
        if($statusPublish == null){
            $publish = 0;
        }else{
            $publish = 1;
        }

        $title = $data['title'];

        $data = $this->getCollectedData($getListCollectData, $viewType, $data);

        $data['image'] = $dokumentImage;
        $data['thumbnail_img'] = $dokumentThumbnailImage;
        $data['publish_status'] = $publish;
        $data['slugs'] = create_slugs($title);
        $getData = $this->crud->store($data);

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }
    }

    public function update($id)
    {
        $this->callPermission();

        $viewType = 'edit';

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        if($this->request->get('klinik_id') <= 0) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'klinik_id' => __('general.data_empty')
                ]);
        }

        $getListCollectData = collectPassingData($this->passingData, $viewType);

        unset($getListCollectData['thumbnail_img_full']);
        unset($getListCollectData['image_full']);

        $validate = $this->setValidateData($getListCollectData, $viewType, $id);
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

        $dokument = $this->request->file('image_full');
        if ($dokument) {
            if ($dokument->getError() != 1) {

                $getFileName = $dokument->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/article';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentImage = Storage::putFile($destinationPath, $dokument);
                }

            }
        }elseif($dokument == null){

            $dokumentImage =  $getData->image;

        }

        $dokumentThumbnail = $this->request->file('thumbnail_img_full');
        if ($dokumentThumbnail) {
            if ($dokumentThumbnail->getError() != 1) {

                $getFileName = $dokumentThumbnail->getClientOriginalName();
                $ext = explode('.', $getFileName);
                $ext = end($ext);
                $destinationPath = 'synapsaapps/article';
                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'svg', 'gif'])) {

                    $dokumentThumbnailImage = Storage::putFile($destinationPath, $dokumentThumbnail);
                }

            }
        }elseif($dokumentThumbnail == null){

            $dokumentThumbnailImage =  $getData->thumbnail_img;

        }

        $statusPublish = $this->request->get('publish_status');
        //dd($statusPublish);
        if($statusPublish == null){
            $publish = 0;
        }else{
            $publish = 1;
        }

        $title = $data['title'];

        $data = $this->getCollectedData($getListCollectData, $viewType, $data, $getData);

        foreach ($getListCollectData as $key => $val) {
            if($val['type'] == 'image_many') {
                $getStorage = explode(',', $this->request->get($key.'_storage')) ?? [];
                $getOldData = json_decode($getData->$key, true);
                $tempData = [];
                if ($getOldData) {
                    foreach ($getOldData as $index => $value) {
                        if (in_array($index, $getStorage)) {
                            $tempData[] = $value;
                        }
                    }
                }
                if (isset($data[$key])) {
                    foreach (json_decode($data[$key], true) as $index => $value) {
                        $tempData[] = $value;
                    }
                }
                $data[$key] = json_encode($tempData);
            }
        }

        $data['image'] = $dokumentImage;
        $data['thumbnail_img'] = $dokumentThumbnailImage;
        $data['publish_status'] = $publish;
        $data['slugs'] = create_slugs($title);

        $getData = $this->crud->update($data, $id);

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_edit_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_edit_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route($this->rootRoute.'.' . $this->route . '.show', $id);
        }
    }


}
