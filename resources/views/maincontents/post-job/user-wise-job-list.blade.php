<?php
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
use App\Models\PostJob;
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
            <div class="card-body">
               <table id="simpletable" class="table table-striped table-bordered nowrap">
                  <thead>
                     <tr>
                     <th scope="col">#</th>
                     <th scope="col">Role</th>
                     <th scope="col">Name</th>
                     <th scope="col">Email</th>
                     <th scope="col">Country Code</th>
                     <th scope="col">Phone</th>
                     <th scope="col">Jobs</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if(count($subusers)>0){ $sl=1; foreach($subusers as $subuser){?>
                           <tr>
                              <th scope="row"><?=$sl++?></th>
                              <td><?=$subuser->role_name?></td>
                              <td><?=$subuser->first_name.' '.$subuser->last_name?></td>
                              <td><?=$subuser->email?></td>
                              <td><?=$subuser->country_code?></td>
                              <td><?=$subuser->phone?></td>
                              <td>
                                <?php
                                echo $job_count = PostJob::where('status', '!=', 3)->where('created_by', '=', $subuser->id)->count();
                                ?>
                              </td>
                           </tr>
                     <?php } }?>
                  </tbody>
               </table>
            </div>
        </div>
      </div>
   </div>
</div>
@endsection

@section('scripts')
<script src="<?=config('constants.admin_assets_url')?>assets/js/table.js"></script>
@endsection