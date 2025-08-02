<?php
use App\Models\PostJob;
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
            <div class="card-body">
               <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="row">
                     <div class="col-md-6">
                        <label for="name" class="form-label">Upload Title <small class="text-danger">*</small></label>
                        <input class="form-control" type="text" id="name" name="name" required placeholder="Upload Title" autofocus />
                     </div>
                     <div class="col-md-6">
                        <label for="upload_file" class="form-label">Upload File <small class="text-danger">*</small></label>
                        <input class="form-control" type="file" id="upload_file" name="upload_file" required />
                        <small class="text-danger">(Only csv files are allowed)</small>
                        <a href="<?=url('/public/material/backend/sample-post-job-file-v2.csv')?>" class="text-primary" target="_blank">Sample File</a>
                     </div>
                  </div>
                  <div class="mt-2">
                     <button type="submit" class="btn btn-primary btn-sm me-2">Upload</button>
                  </div>
               </form>
            </div>
            <div class="card-body">
               <table class="table table-striped">
                  <thead>
                     <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Uploaded Job Count</th>
                        <th>Upload File</th>
                        <th>Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php $sl=1; if(count($rows) > 0){ foreach($rows as $row){?>
                        <tr>
                           <td><?=$sl++?></td>
                           <td><?=$row->name?></td>
                           <td>
                              <?php
                              echo $getJobCount = PostJob::where('upload_id', '=', $row->id)->count();
                              ?>
                           </td>
                           <td>
                              <a href="<?=url('/public/uploads/post-job/' . $row->upload_file)?>" target="_blank" class="badge bg-info"><i class="fas fa-file"></i> View File</a>
                           </td>
                           <td>
                              <a href="<?=url('/upload-post-job/delete/' . Helper::encoded($row->id))?>" onclick="return confirm('Do you want to delete this ?');"><i class="fas fa-trash text-danger"></i></a>
                           </td>
                        </tr>
                     <?php } } else {?>
                        <tr>
                           <td colspan="4" style="text-align: center; color:red;">No uploads available</td>
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