<?php

namespace App\Http\Controllers\Admin;

use App\Codes\Logic\_CrudController;
use App\Codes\Models\V1\ArticleCategory;
use App\Http\Controllers\Controller;
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
            'article_category_id' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'title' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'slugs' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'thumbnail_img' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'image' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'content' => [
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
            ],
            'publish_status' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'publish_date' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'created_by' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'updated_by' => [
                'validate' => [
                    'create' => 'required',
                    'edit' => 'required'
                ],
            ],
            'action' => [
                'create' => 0,
                'edit' => 0,
                'show' => 0,
                'custom' => ',orderable:false'
            ]
        ];

        parent::__construct(
            $request, 'general.article', 'article', 'Article', 'article',
            $passingData
        );


        $getArticleCategory = ArticleCategory::where('status', 1)->pluck('name', 'id')->toArray();
        $listArticleCategory = [0 => 'Kosong'];
        if($getArticleCategory) {
            foreach($getArticleCategory as $key => $value) {
                $listArticleCategory[$key] = $value;
            }
        }


        $this->data['listSet']['article_category_id'] = $listArticleCategory;
        $this->listView['create'] = env('ADMIN_TEMPLATE').'.page.article.forms';
        $this->listView['show'] = env('ADMIN_TEMPLATE').'.page.article.forms';
        $this->listView['edit'] = env('ADMIN_TEMPLATE').'.page.article.forms';

    }

    public function store()
    {
        $this->callPermission();

        $view_type = 'create';

        $getListCollectData = collectPassingData($this->passingData, $view_type);
        $validate = $this->setValidateData($getListCollectData, $view_type);
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

        $data = $this->getCollectedData($getListCollectData, $view_type, $data);

        $permission = getValidatePermissionMenu($this->request->get('permission'));

        $data['permission_data'] = json_encode($permission);
        $data['permission_route'] = json_encode(getPermissionRouteList($permission));

        $getData = $this->crud->store($data);

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_add_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_add_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route('admin.' . $this->route . '.show', $id);
        }
    }

    public function update($id)
    {
        $this->callPermission();

        $view_type = 'edit';

        $getData = $this->crud->show($id);
        if (!$getData) {
            return redirect()->route('admin.' . $this->route . '.index');
        }

        $getListCollectData = collectPassingData($this->passingData, $view_type);
        $validate = $this->setValidateData($getListCollectData, $view_type, $id);
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

        $data = $this->getCollectedData($getListCollectData, $view_type, $data, $getData);

        $permission = getValidatePermissionMenu($this->request->get('permission'));

        $data['permission_data'] = json_encode($permission);
        $data['permission_route'] = json_encode(getPermissionRouteList($permission));

        $getData = $this->crud->update($data, $id);

        $id = $getData->id;

        if($this->request->ajax()){
            return response()->json(['result' => 1, 'message' => __('general.success_edit_', ['field' => $this->data['thisLabel']])]);
        }
        else {
            session()->flash('message', __('general.success_edit_', ['field' => $this->data['thisLabel']]));
            session()->flash('message_alert', 2);
            return redirect()->route('admin.' . $this->route . '.show', $id);
        }
    }
}
