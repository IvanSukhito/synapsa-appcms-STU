<?php

namespace App\Http\Controllers\API\V1;

use App\Codes\Models\Settings;
use App\Codes\Models\V1\Article;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    protected $request;
    protected $setting;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setting = Cache::remember('settings', env('SESSION_LIFETIME'), function () {
            return Settings::pluck('value', 'key')->toArray();
        });
    }

    public function getArticle(){

        $user = $this->request->attributes->get('_user');

        $limit = 10;
        $data = Article::where('klinik_id', '=', $user->klinik_id)
            ->where('publish_date', '<=', date('Y-m-d'))->where('publish_status', 1)
            ->orderBy('publish_date','DESC')->paginate($limit);

        return response()->json([
            'success' => 1,
            'data' => $data,
            'token' => $this->request->attributes->get('_refresh_token'),
        ]);

    }

    public function getArticleDetail($id){

        $user = $this->request->attributes->get('_user');

        $data = Article::where('klinik_id', '=', $user->klinik_id)
            ->where('publish_date', '<=', date('Y-m-d'))->where('publish_status', 1)
            ->where('id', $id)->first();
        if (!$data) {
            return response()->json([
                'success' => 0,
                'message' => ['Artikel Tidak Ditemukan'],
                'token' => $this->request->attributes->get('_refresh_token'),
            ], 404);
        }
        else {
            return response()->json([
                'success' => 1,
                'data' => $data,
                'token' => $this->request->attributes->get('_refresh_token'),
            ]);
        }

    }

}
