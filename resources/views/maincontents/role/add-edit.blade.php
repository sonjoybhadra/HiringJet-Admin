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
                $role_name      = $row->role_name;
                $status         = $row->status;
                $module_id      = (($row->module_id != '')?json_decode($row->module_id):[]);
            } else {
                $role_name      = '';
                $status         = '';
                $module_id      = [];
            }
            ?>
            <div class="card-body">
                <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label for="role_name" class="form-label">Name <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="role_name" name="role_name" value="<?=$role_name?>" required placeholder="Name" autofocus />
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label d-block">Status <small class="text-danger">*</small></label>
                            <div class="form-check form-switch mt-0 ">
                                <input class="form-check-input" type="checkbox" name="status" role="switch" id="status" <?=(($status == 1)?'checked':'')?>>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            <label for="status" class="form-label d-block">Modules</label>
                            <div class="row">
                                <?php if($modules){ foreach($modules as $module){?>
                                    <div class="col-md-3 col-lg-3">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="module_id[]" value="<?=$module->id?>" id="module<?=$module->id?>" <?=((in_array($module->id, $module_id))?'checked':'')?>>
                                            <label class="form-check-label" for="module<?=$module->id?>"><?=$module->name?></label>
                                        </div>
                                    </div>
                                <?php } }?>
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