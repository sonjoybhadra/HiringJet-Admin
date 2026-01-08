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
                $title                  = $row->title;
                $page_slug              = $row->page_slug;
                $meta_title             = $row->meta_title;
                $meta_keywords          = $row->meta_keywords;
                $meta_description       = $row->meta_description;
                $og_image               = $row->og_image;
                $status                 = $row->status;
            } else {
                $id                     = '';
                $title                  = '';
                $page_slug              = '';
                $meta_title             = '';
                $meta_keywords          = '';
                $meta_description       = '';
                $og_image               = '';
                $status                 = '';
            }
            ?>
            <div class="card-body">
                <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-5">
                            <label for="title" class="form-label">Title <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="title" name="title" value="<?=$title?>" required placeholder="Title" autofocus />
                        </div>
                        <div class="col-md-5">
                            <label for="page_slug" class="form-label">Page Slug <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="page_slug" name="page_slug" value="<?=$page_slug?>" required placeholder="Page Slug" autofocus />
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label d-block">Status <small class="text-danger">*</small></label>
                            <div class="form-check form-switch mt-0 ">
                                <input class="form-check-input" type="checkbox" name="status" role="switch" id="status" <?=(($status == 1)?'checked':'')?>>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="meta_title" class="form-label">Meta Title <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="meta_title" name="meta_title" placeholder="Meta Title" rows="5"><?=$meta_title?></textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="meta_keywords" class="form-label">Meta Keywords <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="meta_keywords" name="meta_keywords" placeholder="Meta Keywords" rows="5"><?=$meta_keywords?></textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="meta_description" class="form-label">Meta Description <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="meta_description" name="meta_description" placeholder="Meta Description" rows="5"><?=$meta_description?></textarea>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start align-items-sm-center gap-4 mt-3">
                                <div class="button-wrapper">
                                    <label for="og_image" class="btn btn-primary me-2 mb-4" tabindex="0">
                                        <span class="d-none d-sm-block">Upload OG Image</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input type="file" id="og_image" class="account-file-input" name="og_image" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                    </label>
                                    <?php
                                    if(!empty($row)){
                                        $pageLink = Request::url();
                                    ?>
                                        <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/seo_pages/og_image/id/' . $id)?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                            <i class="bx bx-reset d-block d-sm-none"></i>
                                            <span class="d-none d-sm-block">Reset</span>
                                        </a>
                                    <?php }?>
                                    <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <img src="<?=(($og_image != '')?config('constants.app_url') . config('constants.uploads_url_path') . $og_image:config('constants.no_image'))?>" alt="<?=$title?>" class="img-thumbnail mt-3" height="200" width="200" id="uploadedAvatar" />
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

<!-- Render somewhere -->
@endsection