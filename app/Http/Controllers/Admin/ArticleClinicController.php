<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\ArticleCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Codes\Models\V1\Article;
use Yajra\DataTables\DataTables;

class ArticleClinicController extends _CrudController
{
    public function __construct(Request $request)
    {
        $passingData = [
            'id' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0
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
                'type' => 'textarea',
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
            'publish_date' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
                'type' => 'datepicker'
            ],
            'publish_status' => [
                'validate' => [
                    'create' => '',
                    'edit' => ''
                ],
                'type' => 'checkbox',
                'lang' => 'general.publish',
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'custom' => ',orderable:false'
            ]
        ];

        parent::__construct(
            $request, 'general.article_clinic', 'article-clinic', 'V1\Article', 'article-clinic',
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

        $adminClinicId = session()->get('admin_clinic_id');
        if(!$adminClinicId) {
            session()->flash('message', __('Tidak ada clinic yang di assign'));
            session()->flash('message_alert', 1);
            return redirect()->route('admin');
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
        $data['klinik_id'] = $adminClinicId;
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

    public function edit($id)
    {
        $this->callPermission();

        $adminClinicId = session()->get('admin_clinic_id');
        if(!$adminClinicId) {
            session()->flash('message', __('Tidak ada clinic yang di assign'));
            session()->flash('message_alert', 1);
            return redirect()->route('admin');
        }

        $getData = $this->crud->show($id,[
            'id' => $id,
            'klinik_id' => $adminClinicId,
        ]);

        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
        }

        $data = $this->data;

        $data['viewType'] = 'edit';
        $data['formsTitle'] = __('general.title_edit', ['field' => $data['thisLabel']]);
        $data['passing'] = collectPassingData($this->passingData, $data['viewType']);
        $data['data'] = $getData;

        return view($this->listView[$data['viewType']], $data);
    }

    public function update($id)
    {
        $this->callPermission();

        $viewType = 'edit';

        $adminClinicId = session()->get('admin_clinic_id');
        if(!$adminClinicId) {
            session()->flash('message', __('Tidak ada clinic yang di assign'));
            session()->flash('message_alert', 1);
            return redirect()->route('admin');
        }

        $getData = $this->crud->show($id,[
            'id' => $id,
            'klinik_id' => $adminClinicId,
        ]);

        if (!$getData) {
            return redirect()->route($this->rootRoute.'.' . $this->route . '.index');
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
        $data['klinik_id'] = $adminClinicId;
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

    public function dataTable()
    {
        $this->callPermission();

        $adminClinicId = session()->get('admin_clinic_id');

        $dataTables = new DataTables();

        $builder = $this->model::query()->select('*')->where('klinik_id', $adminClinicId);

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
            if (in_array($list['type'], ['select', 'select2', 'multiselect2'])) {
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName) {
                    $getList = isset($this->data['listSet'][$fieldName]) ? $this->data['listSet'][$fieldName] : [];
                    return isset($getList[$query->$fieldName]) ? $getList[$query->$fieldName] : $query->$fieldName;
                });
            }
            else if (in_array($list['type'], ['image', 'image_preview'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    if ($query->{$fieldName.'_full'}) {
                        return '<img src="' . $query->{$fieldName.'_full'}. '" class="img-responsive max-image-preview"/>';
                    }
                    return '<img src="' . asset($list['path'] . $query->$fieldName) . '" class="img-responsive max-image-preview"/>';
                });
            }
            else if (in_array($list['type'], ['code'])) {
                $listRaw[] = $fieldName;
                $dataTables = $dataTables->editColumn($fieldName, function ($query) use ($fieldName, $list, $listRaw) {
                    return '<pre>' . json_encode(json_decode($query->$fieldName, true), JSON_PRETTY_PRINT) . '</pre>';
                });
            }
            else if (in_array($list['type'], ['texteditor'])) {
                $listRaw[] = $fieldName;
            }
        }

        return $dataTables
            ->rawColumns($listRaw)
            ->make(true);
    }


}
