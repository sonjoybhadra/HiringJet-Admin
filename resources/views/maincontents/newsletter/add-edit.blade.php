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
                $title          = $row->title;
                $description    = $row->description;
                $to_users       = $row->to_users;
                $users          = json_decode($row->users);
                $status         = $row->status;
            } else {
                $title          = '';
                $description    = '';
                $to_users       = '';
                $users          = [];
                $status         = '';
            }
            ?>
            <div class="card-body">
                <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label for="faq_category_id" class="form-label">User Type</label>
                            <select class="form-control" name="to_users" id="to_users" onchange="getUsers(this.value);">
                                <option value="" selected>Select User Type</option>
                                <option value="0" <?=(($to_users == 0)?'selected':'')?>>All</option>
                                <option value="1" <?=(($to_users == 1)?'selected':'')?>>Jobseeker</option>
                                <option value="2" <?=(($to_users == 2)?'selected':'')?>>Employer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="users" class="form-label">Users</label>
                            <select class="select2" id="all_users" name="all_users[]" multiple style="display:none;">
                                <?php if($all_users){ foreach($all_users as $all_user){?>
                                    <option value="<?=$all_user->id?>" <?=((in_array($all_user->id, $users))?'selected':'')?>><?=$all_user->first_name . ' ' . $all_user->last_name?></option>
                                <?php } }?>
                            </select>
                            <select class="select2" id="jobseeker_users" name="jobseeker_users[]" multiple style="display:none;">
                                <?php if($jobseeker_users){ foreach($jobseeker_users as $jobseeker_user){?>
                                    <option value="<?=$jobseeker_user->id?>" <?=((in_array($jobseeker_user->id, $users))?'selected':'')?>><?=$jobseeker_user->first_name . ' ' . $jobseeker_user->last_name?></option>
                                <?php } }?>
                            </select>
                            <select class="select2" id="employer_users" name="employer_users[]" multiple style="display:none;">
                                <?php if($employer_users){ foreach($employer_users as $employer_user){?>
                                    <option value="<?=$employer_user->id?>" <?=((in_array($employer_user->id, $users))?'selected':'')?>><?=$employer_user->first_name . ' ' . $employer_user->last_name?></option>
                                <?php } }?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="title" class="form-label">Title</label>
                            <textarea name="title" class="form-control" id="title" rows="5" required><?=$title?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" class="form-control" id="ckeditor1"><?=$description?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label d-block">Status <small class="text-danger">*</small></label>
                            <div class="form-check form-switch mt-0 ">
                                <input class="form-check-input" type="checkbox" name="status" role="switch" id="status" <?=(($status == 1)?'checked':'')?>>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
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
    <!-- Vendors JS -->
    <script src="<?= config('constants.admin_assets_url') ?>assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
    <script src="<?= config('constants.admin_assets_url') ?>assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
    <script src="<?= config('constants.admin_assets_url') ?>assets/vendor/libs/select2/select2.js"></script>
    <script src="<?= config('constants.admin_assets_url') ?>assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="<?= config('constants.admin_assets_url') ?>assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="<?= config('constants.admin_assets_url') ?>assets/vendor/libs/@form-validation/auto-focus.js"></script>
    <script>
        $(function(){
            var to_users = $('#to_users').val();
            if(to_users == 0){
                $('#all_users').show();
                $('#all_users').attr('required', true);

                $('#jobseeker_users').hide();
                $('#jobseeker_users').attr('required', false);

                $('#employer_users').hide();
                $('#employer_users').attr('required', false);
            } else if(to_users == 1){
                $('#all_users').hide();
                $('#all_users').attr('required', false);

                $('#jobseeker_users').show();
                $('#jobseeker_users').attr('required', true);

                $('#employer_users').hide();
                $('#employer_users').attr('required', false);
            } else if(to_users == 2){
                $('#all_users').hide();
                $('#all_users').attr('required', false);

                $('#jobseeker_users').hide();
                $('#jobseeker_users').attr('required', false);

                $('#employer_users').show();
                $('#employer_users').attr('required', true);
            }
        })
    </script>
@endsection