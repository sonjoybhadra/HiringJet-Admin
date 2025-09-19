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
        <h4><?= $page_header ?></h4>
        <h6 class="breadcrumb-wrapper">
            <span class="text-muted fw-light"><a href="<?= url('dashboard') ?>">Dashboard</a> /</span>
            <span class="text-muted fw-light"><a href="<?= url($controllerRoute . '/list/') ?>"><?= $module['title'] ?> List</a> /</span>
            <?= $page_header ?>
        </h6>
        <div class="nav-align-top mb-4">
            <?php if (session('success_message')) { ?>
                <div class="alert alert-success alert-dismissible autohide" role="alert">
                    <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-desktop align-top me-2"></i>Success!</h6>
                    <span><?= session('success_message') ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>
                </div>
            <?php } ?>
            <?php if (session('error_message')) { ?>
                <div class="alert alert-danger alert-dismissible autohide" role="alert">
                    <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-store align-top me-2"></i>Error!</h6>
                    <span><?= session('error_message') ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>
                </div>
            <?php } ?>
            <div class="card mb-4">
                <?php
                if ($row) {
                    $section1         = (($row->section1 != '') ? json_decode($row->section1) : []);
                    $section2         = (($row->section2 != '') ? json_decode($row->section2) : []);
                    $section3         = (($row->section3 != '') ? json_decode($row->section3) : []);
                    $section4         = (($row->section4 != '') ? json_decode($row->section4) : []);
                    $section5         = (($row->section5 != '') ? json_decode($row->section5) : []);
                } else {
                    $section1         = [];
                    $section2         = [];
                    $section3         = [];
                    $section4         = [];
                    $section5         = [];
                }
                ?>
                <div class="card-body">
                    <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- section 1 -->
                        <div class="row mt-2" style="border:1px solid #092b61; padding: 10px; border-radius: 10px;">
                            <h5>Section 1</h5>
                            <div class="col-md-3">
                                <label for="section1_title" class="form-label">Section 1 Title <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="section1_title" name="section1_title" value="<?= ((!empty($section1)) ? $section1->title : '') ?>" required placeholder="Section 1 Title" autofocus />
                            </div>
                            <div class="col-md-3">
                                <label for="section1_button_text" class="form-label">Section 1 Button Text <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="section1_button_text" name="section1_button_text" value="<?= ((!empty($section1)) ? $section1->button_text : '') ?>" placeholder="Section 1 Button Text" />
                            </div>
                            <div class="col-md-6">
                                <label for="section1_description" class="form-label">Section 1 Description <small class="text-danger">*</small></label>
                                <textarea class="form-control" id="section1_description" name="section1_description" placeholder="Section 1 Description" rows="5"><?= ((!empty($section1)) ? $section1->description : '') ?></textarea>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <!-- section 1 -->

                        <!-- section 2 -->
                        <div class="row mt-2" style="border:1px solid #092b61; padding: 10px; border-radius: 10px;">
                            <h5>Section 2</h5>

                            <div class="col-md-12">
                                <label for="section2_title" class="form-label">Section 1 Title <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="section2_title" name="section2_title" value="<?= ((!empty($section2)) ? $section2->title : '') ?>" required placeholder="Section 1 Title" autofocus />
                            </div>
                            <?php
                            $box2 = ((!empty($section2)) ? ((!empty($section2->box))?$section2->box:[]) : []);
                            ?>
                            <div class="col-md-12">
                                <div class="field_wrapper2 mt-3">
                                    <?php if (!empty($box2)) {
                                        for ($k = 0; $k < count($box2); $k++) { ?>
                                            <div class="row" style="border:1px solid #3c6df085; padding: 10px; border-radius: 10px; margin-bottom: 5px;">
                                                <div class="col-lg-4 col-md-4">
                                                    <label class="form-label">Section 2 Box Name</label>
                                                    <input type="text" class="form-control" name="section2_box_text[]" value="<?= $box2[$k]->box_text ?>" placeholder="Section 2 Box Name" />
                                                </div>
                                                <div class="col-lg-3 col-md-3">
                                                    <label class="form-label">Section 2 Box Link</label>
                                                    <input type="text" class="form-control" name="section2_box_link[]" value="<?= $box2[$k]->box_link ?>" placeholder="Section 2 Box Link" />
                                                </div>
                                                <div class="col-lg-3 col-md-3">
                                                    <label class="form-label">Section 2 Box Image</label><br>
                                                    <input type="file" class="account-file-input" name="section2_box_image[]" accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                                    <?php if (!empty($box2[$k]->box_image)) {
                                                        if ($box2[$k]->box_image != '') { ?>
                                                            <img src="<?= (($box2[$k]->box_image != '') ? config('constants.app_url') . config('constants.uploads_url_path') . $box2[$k]->box_image : config('constants.no_image')) ?>" alt="<?= $box2[$k]->box_text ?>" class="img-thumbnail mt-3" height="30" width="30" id="uploadedAvatar" />
                                                    <?php }
                                                    } ?>
                                                </div>
                                                <div class="col-lg-2 col-md-2" style="margin-top: 15px;">
                                                    <a href="javascript:void(0);" class="remove_button2" title="Remove row">
                                                        <i class="fa fa-minus-circle fa-2x text-danger"></i>
                                                    </a>
                                                </div>
                                            </div>
                                    <?php }
                                    } ?>
                                    <div class="row" style="border:1px solid #3c6df085; padding: 10px; border-radius: 10px; margin-bottom: 5px;">
                                        <div class="col-lg-4 col-md-4">
                                            <label class="form-label">Section 2 Box Text</label>
                                            <input type="text" class="form-control" name="section2_box_text[]" placeholder="Section 2 Box Text" />
                                        </div>
                                        <div class="col-lg-3 col-md-3">
                                            <label class="form-label">Section 2 Box Link</label>
                                            <input type="text" class="form-control" name="section2_box_link[]" placeholder="Section 2 Box Link" />
                                        </div>
                                        <div class="col-lg-3 col-md-3">
                                            <label class="form-label">Section 2 Box Image</label><br>
                                            <input type="file" class="account-file-input" name="section2_box_image[]" accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                        </div>
                                        <div class="col-lg-2 col-md-2" style="margin-top: 15px;">
                                            <a href="javascript:void(0);" class="add_button2" title="Add row">
                                                <i class="fa fa-plus-circle fa-2x text-success"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <!-- section 2 -->
                                
                        <!-- section 3 -->
                            <div class="row mt-2" style="border:1px solid #092b61; padding: 10px; border-radius: 10px;">
                                <h5>Section 3</h5>

                                <div class="col-md-12">
                                    <label for="section3_title" class="form-label">Section 3 Title <small class="text-danger">*</small></label>
                                    <input class="form-control" type="text" id="section3_title" name="section3_title" value="<?= ((!empty($section3)) ? $section3->title : '') ?>" required placeholder="Section 3 Title" autofocus />
                                </div>
                                <?php
                                $box3 = ((!empty($section3)) ? ((!empty($section3->box))?$section3->box:[]) : []);
                                ?>
                                <div class="col-md-12">
                                    <div class="field_wrapper3 mt-3">
                                        <?php if (!empty($box3)) { for ($k = 0; $k < count($box3); $k++) { ?>
                                            <div class="row" style="border:1px solid #3c6df085; padding: 10px; border-radius: 10px; margin-bottom: 5px;">
                                                <div class="col-lg-3 col-md-3">
                                                    <label class="form-label">Section 3 Box Text</label>
                                                    <input type="text" class="form-control" name="section3_box_text[]" value="<?= $box3[$k]->box_text ?>" placeholder="Section 3 Box Text" />
                                                </div>
                                                <div class="col-lg-5 col-md-5">
                                                    <label class="form-label">Section 3 Box Description</label>
                                                    <textarea class="form-control" name="section3_box_description[]" id="ckeditor<?=($k + 1)?>" placeholder="Section 3 Box Description" rows="5"><?= $box3[$k]->box_description ?></textarea>
                                                </div>
                                                <div class="col-lg-2 col-md-2">
                                                    <label class="form-label">Section 3 Box Image</label><br>
                                                    <input type="file" class="account-file-input" name="section3_box_image[]" accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                                    <?php if (!empty($box3[$k]->box_image)) {
                                                        if ($box3[$k]->box_image != '') { ?>
                                                            <img src="<?= (($box3[$k]->box_image != '') ? config('constants.app_url') . config('constants.uploads_url_path') . $box3[$k]->box_image : config('constants.no_image')) ?>" alt="<?= $box3[$k]->box_text ?>" class="img-thumbnail mt-3" height="150" width="150" id="uploadedAvatar" />
                                                    <?php }
                                                    } ?>
                                                </div>
                                                <div class="col-lg-2 col-md-2" style="margin-top: 15px;">
                                                    <a href="javascript:void(0);" class="remove_button3" title="Remove row">
                                                        <i class="fa fa-minus-circle fa-2x text-danger"></i>
                                                    </a>
                                                </div>

                                                <div class="col-lg-3 col-md-3 mt-3">
                                                    <label class="form-label">Section 3 Box Button 1 Text</label>
                                                    <input type="text" class="form-control" name="section3_box_button1_text[]" value="<?= $box3[$k]->box_button1_text ?>" placeholder="Section 3 Box Button 1 Text" />
                                                </div>
                                                <div class="col-lg-3 col-md-3 mt-3">
                                                    <label class="form-label">Section 3 Box Button 1 Link</label>
                                                    <input type="text" class="form-control" name="section3_box_button1_link[]" value="<?= $box3[$k]->box_button1_link ?>" placeholder="Section 3 Box Button 1 Link" />
                                                </div>
                                                <div class="col-lg-3 col-md-3 mt-3">
                                                    <label class="form-label">Section 3 Box Button 2 Text</label>
                                                    <input type="text" class="form-control" name="section3_box_button2_text[]" value="<?= $box3[$k]->box_button2_text ?>" placeholder="Section 3 Box Button 2 Text" />
                                                </div>
                                                <div class="col-lg-3 col-md-3 mt-3">
                                                    <label class="form-label">Section 3 Box Button 2 Link</label>
                                                    <input type="text" class="form-control" name="section3_box_button2_link[]" value="<?= $box3[$k]->box_button2_link ?>" placeholder="Section 3 Box Button 2 Link" />
                                                </div>
                                            </div>
                                        <?php } } ?>
                                        <div class="row" style="border:1px solid #3c6df085; padding: 10px; border-radius: 10px; margin-bottom: 5px;">
                                            <div class="col-lg-3 col-md-3">
                                                <label class="form-label">Section 3 Box Text</label>
                                                <input type="text" class="form-control" name="section3_box_text[]" placeholder="Section 3 Box Text" />
                                            </div>
                                            <div class="col-lg-5 col-md-5">
                                                <label class="form-label">Section 3 Box Description</label>
                                                <textarea class="form-control" name="section3_box_description[]" id="ckeditor31" placeholder="Section 3 Box Description" rows="5"></textarea>
                                            </div>
                                            <div class="col-lg-2 col-md-2">
                                                <label class="form-label">Section 3 Box Image</label><br>
                                                <input type="file" class="account-file-input" name="section3_box_image[]" accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                            </div>
                                            <div class="col-lg-2 col-md-2" style="margin-top: 15px;">
                                                <a href="javascript:void(0);" class="add_button3" title="Add row">
                                                    <i class="fa fa-plus-circle fa-2x text-success"></i>
                                                </a>
                                            </div>

                                            <div class="col-lg-3 col-md-3 mt-3">
                                                <label class="form-label">Section 3 Box Button 1 Text</label>
                                                <input type="text" class="form-control" name="section3_box_button1_text[]" placeholder="Section 3 Box Button 1 Text" />
                                            </div>
                                            <div class="col-lg-3 col-md-3 mt-3">
                                                <label class="form-label">Section 3 Box Button 1 Link</label>
                                                <input type="text" class="form-control" name="section3_box_button1_link[]" placeholder="Section 3 Box Button 1 Link" />
                                            </div>
                                            <div class="col-lg-3 col-md-3 mt-3">
                                                <label class="form-label">Section 3 Box Button 2 Text</label>
                                                <input type="text" class="form-control" name="section3_box_button2_text[]" placeholder="Section 3 Box Button 2 Text" />
                                            </div>
                                            <div class="col-lg-3 col-md-3 mt-3">
                                                <label class="form-label">Section 3 Box Button 2 Link</label>
                                                <input type="text" class="form-control" name="section3_box_button2_link[]" placeholder="Section 3 Box Button 2 Link" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-2">
                        <!-- section 3 -->

                        <!-- section 4 -->
                        <div class="row mt-2" style="border:1px solid #092b61; padding: 10px; border-radius: 10px;">
                            <h5>Section 4</h5>
                            <div class="col-md-3">
                                <label for="section4_title" class="form-label">Section 4 Title <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="section4_title" name="section4_title" value="<?= ((!empty($section4)) ? $section4->title : '') ?>" required placeholder="Section 4 Title" autofocus />
                            </div>
                            <div class="col-md-3">
                                <label for="section4_button_text" class="form-label">Section 4 Button Text <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="section4_button_text" name="section4_button_text" value="<?= ((!empty($section4)) ? $section4->button_text : '') ?>" placeholder="Section 4 Button Text" />
                            </div>
                            <div class="col-md-6">
                                <label for="section4_description" class="form-label">Section 4 Description <small class="text-danger">*</small></label>
                                <textarea class="form-control" id="section4_description" name="section4_description" placeholder="Section 4 Description" rows="5"><?= ((!empty($section4)) ? $section4->description : '') ?></textarea>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <!-- section 4 -->

                        <!-- section 5 -->
                        <div class="row mt-2" style="border:1px solid #092b61; padding: 10px; border-radius: 10px;">
                            <h5>Section 5</h5>
                            <div class="col-md-6">
                                <label for="section5_title" class="form-label">Section 5 Title <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="section5_title" name="section5_title" value="<?= ((!empty($section5)) ? $section5->title : '') ?>" required placeholder="Section 5 Title" autofocus />
                            </div>
                            <div class="col-md-6">
                                <label for="ckeditor41" class="form-label">Section 5 Description <small class="text-danger">*</small></label>
                                <textarea class="form-control" id="ckeditor41" name="section5_description" placeholder="Section 5 Description" rows="5"><?= ((!empty($section5)) ? $section5->description : '') ?></textarea>
                            </div>
                            <?php
                            $section5_image1 = ((!empty($section5)) ? $section5->image1 : '');
                            $section5_image2 = ((!empty($section5)) ? $section5->image2 : '');
                            $section5_image3 = ((!empty($section5)) ? $section5->image3 : '');
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start align-items-sm-center gap-4 mt-3">
                                    <div class="button-wrapper">
                                        <label for="section5_image1" class="btn btn-primary me-2 mb-4" tabindex="0">
                                            <span class="d-none d-sm-block">Section 5 Image 1</span>
                                            <i class="bx bx-upload d-block d-sm-none"></i>
                                            <input type="file" id="section5_image1" class="account-file-input" name="section5_image1" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                        </label>
                                        <?php
                                        if (!empty($row)) {
                                            $pageLink = Request::url();
                                        ?>
                                            <!-- <a href="<?= url('common-delete-image/' . Helper::encoded($pageLink) . '/home_pages/section5_image1/id/' . 1) ?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                                <i class="bx bx-reset d-block d-sm-none"></i>
                                                <span class="d-none d-sm-block">Reset</span>
                                            </a> -->
                                        <?php } ?>
                                        <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <img src="<?= (($section5_image1 != '') ? config('constants.app_url') . config('constants.uploads_url_path') . $section5_image1 : config('constants.no_image')) ?>" alt="<?= ((!empty($section5)) ? $section5->title : '') ?>" class="img-thumbnail mt-3" height="200" width="200" id="uploadedAvatar" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start align-items-sm-center gap-4 mt-3">
                                    <div class="button-wrapper">
                                        <label for="section5_image2" class="btn btn-primary me-2 mb-4" tabindex="0">
                                            <span class="d-none d-sm-block">Section 5 Image 2</span>
                                            <i class="bx bx-upload d-block d-sm-none"></i>
                                            <input type="file" id="section5_image2" class="account-file-input" name="section5_image2" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                        </label>
                                        <?php
                                        if (!empty($row)) {
                                            $pageLink = Request::url();
                                        ?>
                                            <!-- <a href="<?= url('common-delete-image/' . Helper::encoded($pageLink) . '/home_pages/section5_image2/id/' . 1) ?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                                <i class="bx bx-reset d-block d-sm-none"></i>
                                                <span class="d-none d-sm-block">Reset</span>
                                            </a> -->
                                        <?php } ?>
                                        <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <img src="<?= (($section5_image2 != '') ? config('constants.app_url') . config('constants.uploads_url_path') . $section5_image2 : config('constants.no_image')) ?>" alt="<?= ((!empty($section5)) ? $section5->title : '') ?>" class="img-thumbnail mt-3" height="200" width="200" id="uploadedAvatar" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start align-items-sm-center gap-4 mt-3">
                                    <div class="button-wrapper">
                                        <label for="section5_image3" class="btn btn-primary me-2 mb-4" tabindex="0">
                                            <span class="d-none d-sm-block">Section 5 Image 3</span>
                                            <i class="bx bx-upload d-block d-sm-none"></i>
                                            <input type="file" id="section5_image3" class="account-file-input" name="section5_image3" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                        </label>
                                        <?php
                                        if (!empty($row)) {
                                            $pageLink = Request::url();
                                        ?>
                                            <!-- <a href="<?= url('common-delete-image/' . Helper::encoded($pageLink) . '/home_pages/section5_image3/id/' . 1) ?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                                <i class="bx bx-reset d-block d-sm-none"></i>
                                                <span class="d-none d-sm-block">Reset</span>
                                            </a> -->
                                        <?php } ?>
                                        <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <img src="<?= (($section5_image3 != '') ? config('constants.app_url') . config('constants.uploads_url_path') . $section5_image3 : config('constants.no_image')) ?>" alt="<?= ((!empty($section5)) ? $section5->title : '') ?>" class="img-thumbnail mt-3" height="200" width="200" id="uploadedAvatar" />
                            </div>
                        </div>
                        <hr class="mt-2">
                        <!-- section 5 -->

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary btn-sm me-2">Save Changes</button>
                            <a href="<?= url($controllerRoute . '/list/') ?>" class="btn btn-label-secondary btn-sm">Cancel</a>
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
    $(document).ready(function() {
        var maxField = 10; //Input fields increment limitation
        var addButton = $('.add_button2'); //Add button selector
        var wrapper = $('.field_wrapper2'); //Input field wrapper
        var fieldHTML = `<div class="row" style="border:1px solid #3c6df085; padding: 10px; border-radius: 10px; margin-bottom: 5px;">
                                    <div class="col-lg-4 col-md-4">
                                        <label class="form-label">Section 2 Box Text</label>
                                        <input type="text" class="form-control" name="section2_box_text[]" placeholder="Section 2 Box Text"/>
                                    </div>
                                    <div class="col-lg-3 col-md-3">
                                        <label class="form-label">Section 2 Box Link</label>
                                        <input type="text" class="form-control" name="section2_box_link[]" placeholder="Section 2 Box Link"/>
                                    </div>
                                    <div class="col-lg-3 col-md-3">
                                        <label class="form-label">Section 2 Box Image</label><br>
                                        <input type="file" class="account-file-input" name="section2_box_image[]" accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                    </div>
                                    <div class="col-lg-2 col-md-2" style="margin-top: 15px;">
                                        <a href="javascript:void(0);" class="remove_button2" title="Remove row">
                                            <i class="fa fa-minus-circle fa-2x text-danger"></i>
                                        </a>
                                    </div>
                                </div>`; //New input field html 
        var x = 1; //Initial field counter is 1

        // Once add button is clicked
        $(addButton).click(function() {
            //Check maximum number of input fields
            if (x < maxField) {
                x++; //Increase field counter
                $(wrapper).append(fieldHTML); //Add field html
            } else {
                alert('A maximum of ' + maxField + ' fields are allowed to be added. ');
            }
        });

        // Once remove button is clicked
        $(wrapper).on('click', '.remove_button2', function(e) {
            e.preventDefault();
            $(this).parent('div').parent('div').remove(); //Remove field html
            x--; //Decrease field counter
        });
    });
    $(document).ready(function() {
        var maxField = 10; //Input fields increment limitation
        var addButton = $('.add_button3'); //Add button selector
        var wrapper = $('.field_wrapper3'); //Input field wrapper
        var fieldHTML = `<div class="row" style="border:1px solid #3c6df085; padding: 10px; border-radius: 10px; margin-bottom: 5px;">
                                    <div class="col-lg-3 col-md-3">
                                        <label class="form-label">Section 3 Box Text</label>
                                        <input type="text" class="form-control" name="section3_box_text[]" placeholder="Section 3 Box Text" />
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <label class="form-label">Section 3 Box Description</label>
                                        <textarea class="form-control" name="section3_box_description[]" placeholder="Section 3 Box Description" rows="5"></textarea>
                                    </div>
                                    <div class="col-lg-3 col-md-3">
                                        <label class="form-label">Section 3 Box Image</label><br>
                                        <input type="file" class="account-file-input" name="section3_box_image[]" accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                    </div>
                                    <div class="col-lg-2 col-md-2" style="margin-top: 15px;">
                                        <a href="javascript:void(0);" class="remove_button3" title="Remove row">
                                            <i class="fa fa-minus-circle fa-2x text-danger"></i>
                                        </a>
                                    </div>

                                    <div class="col-lg-3 col-md-3 mt-3">
                                        <label class="form-label">Section 3 Box Button 1 Text</label>
                                        <input type="text" class="form-control" name="section3_box_button1_text[]" placeholder="Section 3 Box Button 1 Text" />
                                    </div>
                                    <div class="col-lg-3 col-md-3 mt-3">
                                        <label class="form-label">Section 3 Box Button 1 Link</label>
                                        <input type="text" class="form-control" name="section3_box_button1_link[]" placeholder="Section 3 Box Button 1 Link" />
                                    </div>
                                    <div class="col-lg-3 col-md-3 mt-3">
                                        <label class="form-label">Section 3 Box Button 2 Text</label>
                                        <input type="text" class="form-control" name="section3_box_button2_text[]" placeholder="Section 3 Box Button 2 Text" />
                                    </div>
                                    <div class="col-lg-3 col-md-3 mt-3">
                                        <label class="form-label">Section 3 Box Button 2 Link</label>
                                        <input type="text" class="form-control" name="section3_box_button2_link[]" placeholder="Section 3 Box Button 2 Link" />
                                    </div>
                                </div>`; //New input field html 
        var x = 1; //Initial field counter is 1

        // Once add button is clicked
        $(addButton).click(function() {
            //Check maximum number of input fields
            if (x < maxField) {
                x++; //Increase field counter
                $(wrapper).append(fieldHTML); //Add field html
            } else {
                alert('A maximum of ' + maxField + ' fields are allowed to be added. ');
            }
        });

        // Once remove button is clicked
        $(wrapper).on('click', '.remove_button3', function(e) {
            e.preventDefault();
            $(this).parent('div').parent('div').remove(); //Remove field html
            x--; //Decrease field counter
        });
    });
</script>
@endsection