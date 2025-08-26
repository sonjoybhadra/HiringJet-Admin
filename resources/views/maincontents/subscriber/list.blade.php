<?php
use App\Models\User;
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
?>
@extends('layouts.main')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
   <div class="row g-6">
      <h4><?=$page_header?></h4>
      <h6 class="breadcrumb-wrapper">
         <span class="text-muted fw-light"><a href="<?=url('dashboard')?>">Dashboard</a> /</span> <?=$page_header?>
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
            <div class="card-header">
               
            </div>
            <div class="card-body">
               <table id="simpletable" class="table table-striped table-bordered nowrap">
                  <thead>
                     <tr>
                     <th scope="col">#</th>
                     <th scope="col">Email</th>
                     <th scope="col">IP Address</th>
                     <th scope="col">Created At</th>
                     <th scope="col">Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if(count($rows)>0){ $sl=1;$total_job=0; foreach($rows as $row){?>
                           <tr>
                              <th scope="row"><?=$sl++?></th>
                              <td><?=$row->email?></td>
                              <td><?=$row->ip?></td>
                              <td><?=$row->created_at?></td>
                              <td>
                                 <a href="<?=url($controllerRoute . '/delete/'.Helper::encoded($row->id))?>" class="btn btn-outline-danger btn-sm" title="Delete <?=$module['title']?>" onclick="return confirm('Do You Want To Delete This <?=$module['title']?>');"><i class="fa fa-trash"></i></a>
                              </td>
                           </tr>
                     <?php } } else {?>
                           <tr>
                              <td colspan="5" style="color:red; text-align:center;">No records found</td>
                           </tr>
                     <?php }?>
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