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
                $page_name              = $row->page_name;
                $page_content           = $row->page_content;
                $page_banner_image      = $row->page_banner_image;
                $page_image             = $row->page_image;
                $meta_title             = $row->meta_title;
                $meta_keywords          = $row->meta_keywords;
                $meta_description       = $row->meta_description;
                $status                 = $row->status;
            } else {
                $id                     = '';
                $page_name              = '';
                $page_content           = '';
                $page_banner_image      = '';
                $page_image             = '';
                $meta_title             = '';
                $meta_keywords          = '';
                $meta_description       = '';
                $status                 = '';
            }
            ?>
            <div class="card-body">
                <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label for="page_name" class="form-label">Page Name <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="page_name" name="page_name" value="<?=$page_name?>" required placeholder="Page Name" autofocus />
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label d-block">Status <small class="text-danger">*</small></label>
                            <div class="form-check form-switch mt-0 ">
                                <input class="form-check-input" type="checkbox" name="status" role="switch" id="status" <?=(($status == 1)?'checked':'')?>>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="ckeditor1" class="form-label">Page Content <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="ckeditor1" name="page_content" placeholder="Page Content"><?=$page_content?></textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start align-items-sm-center gap-4 mt-3">
                                <div class="button-wrapper">
                                    <label for="page_banner_image" class="btn btn-primary me-2 mb-4" tabindex="0">
                                        <span class="d-none d-sm-block">Upload Page Banner Image</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input type="file" id="page_banner_image" class="account-file-input" name="page_banner_image" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                    </label>
                                    <?php
                                    if(!empty($row)){
                                        $pageLink = Request::url();
                                    ?>
                                        <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/pages/page_banner_image/id/' . $id)?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                            <i class="bx bx-reset d-block d-sm-none"></i>
                                            <span class="d-none d-sm-block">Reset</span>
                                        </a>
                                    <?php }?>
                                    <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <img src="<?=(($page_banner_image != '')?config('constants.app_url') . config('constants.uploads_url_path') . $page_banner_image:config('constants.no_image'))?>" alt="<?=$page_name?>" class="img-thumbnail mt-3" height="200" width="200" id="uploadedAvatar" />
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start align-items-sm-center gap-4 mt-3">
                                <div class="button-wrapper">
                                    <label for="page_image" class="btn btn-primary me-2 mb-4" tabindex="0">
                                        <span class="d-none d-sm-block">Upload Page Image</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input type="file" id="page_image" class="account-file-input" name="page_image" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                    </label>
                                    <?php
                                    if(!empty($row)){
                                        $pageLink = Request::url();
                                    ?>
                                        <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/pages/page_image/id/' . $id)?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                            <i class="bx bx-reset d-block d-sm-none"></i>
                                            <span class="d-none d-sm-block">Reset</span>
                                        </a>
                                    <?php }?>
                                    <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <img src="<?=(($page_image != '')?config('constants.app_url') . config('constants.uploads_url_path') . $page_image:config('constants.no_image'))?>" alt="<?=$page_name?>" class="img-thumbnail mt-3" height="200" width="200" id="uploadedAvatar" />
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="ckeditor2" class="form-label">Meta Title <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="ckeditor2" name="meta_title" placeholder="Meta Title"><?=$meta_title?></textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="ckeditor3" class="form-label">Meta Keywords <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="ckeditor3" name="meta_keywords" placeholder="Meta Keywords"><?=$meta_keywords?></textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="ckeditor4" class="form-label">Meta Description <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="ckeditor4" name="meta_description" placeholder="Meta Description"><?=$meta_description?></textarea>
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