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
                $id                         = $row->id;
                $first_name                 = $row->first_name;
                $last_name                  = $row->last_name;
                $email                      = $row->email;
                $country_code               = $row->country_code;
                $phone                      = $row->phone;
                $business_id                = $row->business_id;
                $designation_id             = $row->designation_id;
            } else {
                $id                         = '';
                $first_name                 = '';
                $last_name                  = '';
                $email                      = '';
                $country_code               = '';
                $phone                      = '';
                $business_id                = '';
                $designation_id             = '';
            }
            ?>
            <div class="card-body">
                <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="first_name" name="first_name" value="<?=$first_name?>" required placeholder="First Name" autofocus />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="last_name" name="last_name" value="<?=$last_name?>" required placeholder="Last Name" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <small class="text-danger">*</small></label>
                            <input class="form-control" type="email" id="email" name="email" value="<?=$email?>" required placeholder="Email" />
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="country_code" class="form-label">Country Code <small class="text-danger">*</small></label>
                            <select class="form-control" type="text" id="country_code" name="country_code" required>
                                <option value="" selected>Select Country Code</option>
                                <?php if($countries){ foreach($countries as $country){?>
                                    <option value="<?=$country->country_code?>" <?=(($country->country_code == $country_code)?'selected':'')?>><?=$country->country_code?> (<?=$country->name?>)</option>
                                <?php } }?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="phone" class="form-label">Phone <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="phone" name="phone" value="<?=$phone?>" required placeholder="Phone" />
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <small class="text-danger">*</small></label>
                            <input class="form-control" type="password" id="password" name="password" required placeholder="Password" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password <small class="text-danger">*</small></label>
                            <input class="form-control" type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm Password" />
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="business_id" class="form-label">Business Name <small class="text-danger">*</small></label>
                            <select class="form-control" type="text" id="business_id" name="business_id" required>
                                <option value="" selected>Select Business Name</option>
                                <?php if($business){ foreach($business as $bzn){?>
                                    <option value="<?=$bzn->id?>" <?=(($bzn->id == $business_id)?'selected':'')?>><?=$bzn->name?></option>
                                <?php } }?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="designation_id" class="form-label">Designation <small class="text-danger">*</small></label>
                            <select class="form-control" type="text" id="designation_id" name="designation_id" required>
                                <option value="" selected>Select Designation</option>
                                <?php if($designations){ foreach($designations as $designation){?>
                                    <option value="<?=$designation->id?>" <?=(($designation->id == $designation_id)?'selected':'')?>><?=$designation->name?></option>
                                <?php } }?>
                            </select>
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