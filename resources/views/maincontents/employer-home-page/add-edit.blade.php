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
                $section1         = (($row->section1 != '')?json_decode($row->section1):[]);
                $section2         = (($row->section2 != '')?json_decode($row->section2):[]);
                $section3         = (($row->section3 != '')?json_decode($row->section3):[]);
                $section4         = (($row->section4 != '')?json_decode($row->section4):[]);
                $section5         = (($row->section5 != '')?json_decode($row->section5):[]);
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
                            <input class="form-control" type="text" id="section1_title" name="section1_title" value="<?=((!empty($section1))?$section1->title:'')?>" required placeholder="Section 1 Title" autofocus />
                        </div>
                        <div class="col-md-3">
                            <label for="section1_button_text" class="form-label">Section 1 Button Text <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section1_button_text" name="section1_button_text" value="<?=((!empty($section1))?$section1->button_text:'')?>" placeholder="Section 1 Button Text" />
                        </div>
                        <div class="col-md-6">
                            <label for="section1_description" class="form-label">Section 1 Description <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="section1_description" name="section1_description" placeholder="Section 1 Description" rows="5"><?=((!empty($section1))?$section1->description:'')?></textarea>
                        </div>
                    </div>
                    <hr class="mt-2">
                    <!-- section 1 -->
                    
                    <!-- section 4 -->
                    <div class="row mt-2" style="border:1px solid #092b61; padding: 10px; border-radius: 10px;">
                        <h5>Section 4</h5>
                        <div class="col-md-3">
                            <label for="section4_title" class="form-label">Section 4 Title <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section4_title" name="section4_title" value="<?=((!empty($section4))?$section4->title:'')?>" required placeholder="Section 4 Title" autofocus />
                        </div>
                        <div class="col-md-3">
                            <label for="section4_button_text" class="form-label">Section 4 Button Text <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="section4_button_text" name="section4_button_text" value="<?=((!empty($section4))?$section4->button_text:'')?>" placeholder="Section 4 Button Text" />
                        </div>
                        <div class="col-md-6">
                            <label for="section4_description" class="form-label">Section 4 Description <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="section4_description" name="section4_description" placeholder="Section 4 Description" rows="5"><?=((!empty($section4))?$section4->description:'')?></textarea>
                        </div>
                    </div>
                    <hr class="mt-2">
                    <!-- section 4 -->

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
        
    </script>
@endsection