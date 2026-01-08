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
                $name                   = $row->name;
                $price                  = $row->price;
                $cv_storage_limit       = $row->cv_storage_limit;
                $users                  = $row->users;
                $features               = $row->features;
                $ideal_for              = $row->ideal_for;
                $status                 = $row->status;
            } else {
                $id                     = '';
                $name                   = '';
                $price                  = '';
                $cv_storage_limit       = '';
                $users                  = '';
                $features               = '';
                $ideal_for              = '';
                $status                 = '';
            }
            ?>
            <div class="card-body">
                <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <label for="name" class="form-label">Name <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="name" name="name" value="<?=$name?>" required placeholder="Name" autofocus />
                        </div>
                        <div class="col-md-4">
                            <label for="price" class="form-label">Price <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="price" name="price" value="<?=$price?>" required placeholder="Price" />
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label d-block">Status <small class="text-danger">*</small></label>
                            <div class="form-check form-switch mt-0 ">
                                <input class="form-check-input" type="checkbox" name="status" role="switch" id="status" <?=(($status == 1)?'checked':'')?>>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="cv_storage_limit" class="form-label">CV Storage Limit <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="cv_storage_limit" name="cv_storage_limit" value="<?=$cv_storage_limit?>" required placeholder="CV Storage Limit" />
                        </div>
                        <div class="col-md-6">
                            <label for="users" class="form-label">No. Of Users <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="users" name="users" value="<?=$users?>" required placeholder="No. Of Users" />
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="features" class="form-label">Features <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="features" name="features" placeholder="Features" required><?=$features?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="ideal_for" class="form-label">Ideal For <small class="text-danger">*</small></label>
                            <textarea class="form-control" id="ideal_for" name="ideal_for" placeholder="Ideal For" required><?=$ideal_for?></textarea>
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