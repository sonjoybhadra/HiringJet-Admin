<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\FaqCategory;
use App\Models\FaqSubCategory;
use App\Models\Faq;

class FaqController extends BaseApiController
{
    public function getFaqCategory($slug)
    {
        $main_category = FaqCategory::select('id')->where('slug', $slug)->where('status', 1)->first();
        $list = [];
        if($main_category){
            $list = FaqSubCategory::select('id', 'name', 'description', 'image')
                                ->where('faq_category_id', $main_category->id)
                                ->where('status', 1)
                                ->orderBy('id', 'ASC')
                                ->get();
        }
        return $this->sendResponse(
                $list,
                'FAQ Category list.'
            );
    }

    public function getFaqByCategory(Request $request)
    {
        $sql = Faq::select('id', 'question', 'answer')->where('status', 1);
        if($request->category_id){
            $sql->where('faq_category_id', $request->category_id);
        }
        if($request->sub_category_id){
            $sql->where('faq_sub_category_id', $request->sub_category_id);
        }
        $list = $sql->get();
        return $this->sendResponse(
                $list,
                'FAQ list.'
            );
    }

}
