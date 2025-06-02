<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Article;

class ArticleController extends BaseApiController
{
    public function getArticle(Request $request)
    {
        $list = Article::where('status', 1)->get();
        return $this->sendResponse(
                $list,
                'Article list.'
            );
    }
}
