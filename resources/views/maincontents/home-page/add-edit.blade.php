<?php
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.css">
<script src="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/choices.min.js"></script>

<style type="text/css">
    .choices__list--multiple .choices__item {
        background-color: #092b61;
        border: 1px solid #092b61;
    }
</style>
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
                $section1_title             = $row->section1_title;
                $section1_description       = $row->section1_description;
                $section1_button_text       = $row->section1_button_text;
                $section2_title             = $row->section2_title;
                $section2_description       = $row->section2_description;
                $section2_button_text       = $row->section2_button_text;
                $section3_box_image         = $row->section3_box_image;
                $section3_box_text          = $row->section3_box_text;
                $section3_box_number        = $row->section3_box_number;
                $section4_title             = $row->section4_title;
                $section4_country           = (($row->section4_country != '')?json_decode($row->section4_country):[]);
                $section4_city              = (($row->section4_city != '')?json_decode($row->section4_city):[]);
                $section5_title             = $row->section5_title;
                $section5_box_name          = $row->section5_box_name;
                $section5_box_image         = $row->section5_box_image;
                $section6_title             = $row->section6_title;
                $section6_description       = $row->section6_description;
                $section6_button_text       = $row->section6_button_text;
                $section7_title             = $row->section7_title;
                $section7_description       = $row->section7_description;
                $section7_box_name          = $row->section7_box_name;
                $section7_box_description   = $row->section7_box_description;
                $section7_box_image         = $row->section7_box_image;
                $section7_box_link_name     = $row->section7_box_link_name;
                $section7_box_link_url      = $row->section7_box_link_url;
                $section8_title             = $row->section8_title;
                $section8_description       = $row->section8_description;
                $section9_title             = $row->section9_title;
                $section9_description       = $row->section9_description;
                $section10_title            = $row->section10_title;
                $section10_description      = $row->section10_description;
                $section10_image1           = $row->section10_image1;
                $section10_image2           = $row->section10_image2;
                $section10_image3           = $row->section10_image3;
            } else {
                $section1_title             = '';
                $section1_description       = '';
                $section1_button_text       = '';
                $section2_title             = '';
                $section2_description       = '';
                $section2_button_text       = '';

                $section3_box_image         = '';
                $section3_box_text          = '';
                $section3_box_number        = '';

                $section4_title             = '';
                $section4_country           = [];
                $section4_city              = [];

                $section5_title             = '';
                $section5_box_name          = '';
                $section5_box_image         = '';

                $section6_title             = '';
                $section6_description       = '';
                $section6_button_text       = '';

                $section7_title             = '';
                $section7_description       = '';
                $section7_box_name          = '';
                $section7_box_description   = '';
                $section7_box_image         = '';
                $section7_box_link_name     = '';
                $section7_box_link_url      = '';

                $section8_title             = '';
                $section8_description       = '';
                $section9_title             = '';
                $section9_description       = '';
                $section10_title            = '';
                $section10_description      = '';
                $section10_image1           = '';
                $section10_image2           = '';
                $section10_image3           = '';
            }
            ?>
            <div class="card-body">
                <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row mt-2">
                        <h5>Section 1</h5>
                        <div class="col-md-3">
                            <label for="section1_title" class="form-label">Section 1 Title <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section1_title" name="section1_title" value="<?=$section1_title?>" required placeholder="Section 1 Title" autofocus />
                        </div>
                        <div class="col-md-3">
                            <label for="section1_button_text" class="form-label">Section 1 Button Text <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section1_button_text" name="section1_button_text" value="<?=$section1_button_text?>" placeholder="Section 1 Button Text" />
                        </div>
                        <div class="col-md-6">
                            <label for="section1_description" class="form-label">Section 1 Description <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="section1_description" name="section1_description" placeholder="Section 1 Description" rows="5"><?=$section1_description?></textarea>
                        </div>
                    </div>
                    <hr class="mt-2">

                    <div class="row mt-2">
                        <h5>Section 2</h5>
                        <div class="col-md-3">
                            <label for="section2_title" class="form-label">Section 2 Title <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section2_title" name="section2_title" value="<?=$section2_title?>" required placeholder="Section 2 Title" autofocus />
                        </div>
                        <div class="col-md-3">
                            <label for="section2_button_text" class="form-label">Section 2 Button Text <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section2_button_text" name="section2_button_text" value="<?=$section2_button_text?>" placeholder="Section 2 Button Text" />
                        </div>
                        <div class="col-md-6">
                            <label for="section2_description" class="form-label">Section 2 Description <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="section2_description" name="section2_description" placeholder="Section 2 Description" rows="5"><?=$section2_description?></textarea>
                        </div>
                    </div>
                    <hr class="mt-2">



                    <div class="row mt-2">
                        <h5>Section 4</h5>
                        <div class="col-md-4">
                            <label for="section4_title" class="form-label">Section 4 Title <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section4_title" name="section4_title" value="<?=$section4_title?>" required placeholder="Section 4 Title" autofocus />
                        </div>
                        <div class="col-md-4">
                            <label for="section4_country" class="form-label">Country <small class="text-danger">*</small></label>
                            <select class="form-control" name="section4_country[]" id="choices-multiple-remove-button" multiple>
                                <?php if($countries){ foreach($countries as $coun){?>
                                    <option value="<?=$coun->id?>" <?=((in_array($coun->id, $section4_country))?'selected':'')?>><?=$coun->name?></option>
                                <?php } }?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="section4_city" class="form-label">City <small class="text-danger">*</small></label>
                            <select class="form-control" name="section4_city[]" id="choices-multiple-remove-button" multiple>
                                <?php if($cities){ foreach($cities as $city){?>
                                    <option value="<?=$city->id?>" <?=((in_array($city->id, $section4_city))?'selected':'')?>><?=$city->name?></option>
                                <?php } }?>
                            </select>
                        </div>
                    </div>
                    <hr class="mt-2">

                    <div class="row mt-2">
                        <h5>Section 5</h5>
                        <div class="col-md-3">
                            <label for="section5_title" class="form-label">Section 5 Title <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section5_title" name="section5_title" value="<?=$section5_title?>" required placeholder="Section 5 Title" autofocus />
                        </div>
                    </div>
                    <hr class="mt-2">

                    <div class="row mt-2">
                        <h5>Section 6</h5>
                        <div class="col-md-3">
                            <label for="section6_title" class="form-label">Section 6 Title <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section6_title" name="section6_title" value="<?=$section6_title?>" required placeholder="Section 6 Title" autofocus />
                        </div>
                        <div class="col-md-3">
                            <label for="section6_button_text" class="form-label">Section 6 Button Text <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section6_button_text" name="section6_button_text" value="<?=$section6_button_text?>" placeholder="Section 6 Button Text" />
                        </div>
                        <div class="col-md-6">
                            <label for="section6_description" class="form-label">Section 6 Description <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="section6_description" name="section6_description" placeholder="Section 6 Description" rows="5"><?=$section6_description?></textarea>
                        </div>
                    </div>
                    <hr class="mt-2">

                    <div class="row mt-2">
                        <h5>Section 7</h5>
                        <div class="col-md-6">
                            <label for="section7_title" class="form-label">Section 7 Title <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section7_title" name="section7_title" value="<?=$section7_title?>" required placeholder="Section 7 Title" autofocus />
                        </div>
                        <div class="col-md-6">
                            <label for="section7_description" class="form-label">Section 7 Description <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="section7_description" name="section7_description" placeholder="Section 7 Description" rows="5"><?=$section7_description?></textarea>
                        </div>
                    </div>
                    <hr class="mt-2">

                    <div class="row mt-2">
                        <h5>Section 8</h5>
                        <div class="col-md-6">
                            <label for="section8_title" class="form-label">Section 8 Title <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section8_title" name="section8_title" value="<?=$section8_title?>" required placeholder="Section 8 Title" autofocus />
                        </div>
                        <div class="col-md-6">
                            <label for="section8_description" class="form-label">Section 8 Description <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="section8_description" name="section8_description" placeholder="Section 8 Description" rows="5"><?=$section8_description?></textarea>
                        </div>
                    </div>
                    <hr class="mt-2">

                    <div class="row mt-2">
                        <h5>Section 9</h5>
                        <div class="col-md-6">
                            <label for="section9_title" class="form-label">Section 9 Title <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section9_title" name="section9_title" value="<?=$section9_title?>" required placeholder="Section 9 Title" autofocus />
                        </div>
                        <div class="col-md-6">
                            <label for="section9_description" class="form-label">Section 9 Description <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="section9_description" name="section9_description" placeholder="Section 9 Description" rows="5"><?=$section9_description?></textarea>
                        </div>
                    </div>
                    <hr class="mt-2">

                    <div class="row mt-2">
                        <h5>Section 10</h5>
                        <div class="col-md-6">
                            <label for="section10_title" class="form-label">Section 10 Title <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section10_title" name="section10_title" value="<?=$section10_title?>" required placeholder="Section 10 Title" autofocus />
                        </div>
                        <div class="col-md-6">
                            <label for="section10_description" class="form-label">Section 10 Description <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="section10_description" name="section10_description" placeholder="Section 10 Description" rows="5"><?=$section10_description?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start align-items-sm-center gap-4 mt-3">
                                <div class="button-wrapper">
                                    <label for="section10_image1" class="btn btn-primary me-2 mb-4" tabindex="0">
                                        <span class="d-none d-sm-block">Section 10 Image 1</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input type="file" id="section10_image1" class="account-file-input" name="section10_image1" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                    </label>
                                    <?php
                                    if(!empty($row)){
                                        $pageLink = Request::url();
                                    ?>
                                        <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/home_pages/section10_image1/id/' . 1)?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                            <i class="bx bx-reset d-block d-sm-none"></i>
                                            <span class="d-none d-sm-block">Reset</span>
                                        </a>
                                    <?php }?>
                                    <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <img src="<?=(($section10_image1 != '')?$section10_image1:env('NO_IMAGE'))?>" alt="<?=$section10_title?>" class="img-thumbnail mt-3" height="200" width="200" id="uploadedAvatar" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start align-items-sm-center gap-4 mt-3">
                                <div class="button-wrapper">
                                    <label for="section10_image2" class="btn btn-primary me-2 mb-4" tabindex="0">
                                        <span class="d-none d-sm-block">Section 10 Image 2</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input type="file" id="section10_image2" class="account-file-input" name="section10_image2" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                    </label>
                                    <?php
                                    if(!empty($row)){
                                        $pageLink = Request::url();
                                    ?>
                                        <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/home_pages/section10_image2/id/' . 1)?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                            <i class="bx bx-reset d-block d-sm-none"></i>
                                            <span class="d-none d-sm-block">Reset</span>
                                        </a>
                                    <?php }?>
                                    <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <img src="<?=(($section10_image2 != '')?$section10_image2:env('NO_IMAGE'))?>" alt="<?=$section10_title?>" class="img-thumbnail mt-3" height="200" width="200" id="uploadedAvatar" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start align-items-sm-center gap-4 mt-3">
                                <div class="button-wrapper">
                                    <label for="section10_image3" class="btn btn-primary me-2 mb-4" tabindex="0">
                                        <span class="d-none d-sm-block">Section 10 Image 3</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input type="file" id="section10_image3" class="account-file-input" name="section10_image3" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                    </label>
                                    <?php
                                    if(!empty($row)){
                                        $pageLink = Request::url();
                                    ?>
                                        <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/home_pages/section10_image3/id/' . 1)?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                            <i class="bx bx-reset d-block d-sm-none"></i>
                                            <span class="d-none d-sm-block">Reset</span>
                                        </a>
                                    <?php }?>
                                    <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <img src="<?=(($section10_image3 != '')?$section10_image3:env('NO_IMAGE'))?>" alt="<?=$section10_title?>" class="img-thumbnail mt-3" height="200" width="200" id="uploadedAvatar" />
                        </div>
                    </div>
                    <hr class="mt-2">

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
    <script type="text/javascript">
        $(document).ready(function(){    
            var multipleCancelButton = new Choices('#choices-multiple-remove-button', {
                removeItemButton: true,
                maxItemCount:30,
                searchResultLimit:30,
                renderChoiceLimit:30
            });     
        });
    </script>
@endsection