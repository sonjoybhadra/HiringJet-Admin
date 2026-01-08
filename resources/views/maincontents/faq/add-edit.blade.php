<?php
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
?>
@extends('layouts.main')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
   <div class="row g-6">
      <h4><?=$page_header?></h4>
      <h6 class="breadcrumb-wrapper">
            <span class="text-muted fw-light"><a href="<?=url('dashboard')?>">Dashboard</a> /</span>
            <span class="text-muted fw-light"><a href="<?=url($controllerRoute . '/list/')?>"><?=$module['title']?> List</a> /</span>
            <?=$page_header?>
      </h6>
      <div class="nav-align-top mb-4">
         <?php if(session('success_message')){?>
            <div class="alert alert-success alert-dismissible autohide" role="alert">
               <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-desktop align-top me-2"></i>Success!</h6>
               <span><?=session('success_message')?></span>
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
               </button>
            </div>
         <?php }?>
         <?php if(session('error_message')){?>
            <div class="alert alert-danger alert-dismissible autohide" role="alert">
               <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-store align-top me-2"></i>Error!</h6>
               <span><?=session('error_message')?></span>
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
               </button>
            </div>
         <?php }?>
         <div class="card mb-4">
            <?php
            if($row){
                $id                     = $row->id;
                $faq_category_id        = $row->faq_category_id;
                $faq_sub_category_id    = $row->faq_sub_category_id;
                $question               = $row->question;
                $answer                 = $row->answer;
                $status                 = $row->status;
            } else {
                $id                     = '';
                $faq_category_id        = '';
                $faq_sub_category_id    = '';
                $question               = '';
                $answer                 = '';
                $status                 = '';
            }
            ?>
            <div class="card-body">
                <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="faq_category_id" class="form-label">FAQ Category <small class="text-danger">*</small></label>
                            <select class="form-control" type="text" id="faq_category_id" name="faq_category_id" required>
                                <option value="" selected>Select FAQ Category</option>
                                <?php if($cats){ foreach($cats as $cat){?>
                                    <option value="<?=$cat->id?>" <?=(($cat->id == $faq_category_id)?'selected':'')?>><?=$cat->name?></option>
                                <?php } }?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="faq_sub_category_id" class="form-label">FAQ Sub Category <small class="text-danger">*</small></label>
                            <select class="form-control" type="text" id="faq_sub_category_id" name="faq_sub_category_id" required>
                                <option value="" selected>Select FAQ Sub Category</option>
                                <?php if($sub_cats){ foreach($sub_cats as $sub_cat){?>
                                    <option class="faqsubcat faqcat-<?=$sub_cat->faq_category_id?>" value="<?=$sub_cat->id?>" <?=(($sub_cat->id == $faq_sub_category_id)?'selected':'')?>><?=$sub_cat->name?></option>
                                <?php } }?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label d-block">Status <small class="text-danger">*</small></label>
                            <div class="form-check form-switch mt-0 ">
                                <input class="form-check-input" type="checkbox" name="status" role="switch" id="status" <?=(($status == 1)?'checked':'')?>>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="question" class="form-label">Question <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="question" name="question" required placeholder="Question"><?=$question?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="answer" class="form-label">Answer <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="answer" name="answer" required placeholder="Answer"><?=$answer?></textarea>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary btn-sm me-2">Save Changes</button>
                        <a href="<?=url($controllerRoute . '/list/')?>" class="btn btn-label-secondary btn-sm">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
<script>
    $(function(){
        var faq_category_id = '<?=$faq_sub_category_id?>';
        $('#faq_sub_category_id .faqsubcat').hide();
        $('#faq_sub_category_id .faqcat-' + faq_category_id).show();

        $('#faq_category_id').on('change', function(){
            var faq_category_id = $('#faq_category_id').val();
            $('#faq_sub_category_id .faqsubcat').hide();
            $('#faq_sub_category_id .faqcat-' + faq_category_id).show();
        });
    })
</script>
@endsection