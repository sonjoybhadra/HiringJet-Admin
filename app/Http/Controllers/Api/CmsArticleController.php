<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Article;
use App\Models\Page;

class CmsArticleController extends BaseApiController
{
    public function getPage(Request $request)
    {
        $sql = Page::where('status', 1);
        if(!empty($requesr->slug)){
            $sql->where('page_slug', $request->slug);
        }
        $list = $sql->get();
        return $this->sendResponse(
                $list,
                'CMS list.'
            );
    }

    public function getArticle(Request $request)
    {
        $sql = Article::where('status', 1);
        if(!empty($requesr->slug)){
            $sql->where('page_slug', $request->slug);
        }
        $list = $sql->get();
        return $this->sendResponse(
                $list,
                'Article list.'
            );
    }

}
