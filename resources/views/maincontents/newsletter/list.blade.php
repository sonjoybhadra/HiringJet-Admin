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
                <a href="<?=url($controllerRoute . '/add/')?>" class="btn btn-outline-success btn-sm float-end">Add <?=$module['title']?></a>
            </div>
            <div class="card-body">
               <table id="simpletable" class="table table-striped table-bordered nowrap">
                  <thead>
                     <tr>
                     <th scope="col">#</th>
                     <th scope="col">Title</th>
                     <th scope="col">To User</th>
                     <th scope="col">Users</th>
                     <th scope="col">Created At</th>
                     <th scope="col">Is Send</th>
                     <th scope="col">Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if(count($rows)>0){ $sl=1;$total_job=0; foreach($rows as $row){?>
                           <tr>
                              <th scope="row"><?=$sl++?></th>
                              <td><?=$row->title?></td>
                              <td>
                                 <?php if($row->to_users == 0){?>
                                    <span class="badge bg-info">ALL</span>
                                 <?php }?>
                                 <?php if($row->to_users == 1){?>
                                    <span class="badge bg-info">Jobseeker</span>
                                 <?php }?>
                                 <?php if($row->to_users == 2){?>
                                    <span class="badge bg-info">Employer</span>
                                 <?php }?>
                              </td>
                              <td>
                                 <div class="row">
                                    <ul>
                                       <?php
                                       $users = json_decode($value->users);
                                       if(!empty($users)){ for($u=0;$u<count($users);$u++){
                                       $extractUser      = $users[$u];
                                       $getUserInfo      = User::select('first_name', 'last_name')->where('id', '=', $extractUser)->first();
                                       $userName         = (($getUserInfo)?$getUserInfo->first_name.' '.$getUserInfo->last_name:'');
                                       ?>
                                       <div class="col-md-6">
                                          <li><small style="font-size: 10px;"><?=$userName?></small></li>
                                       </div>
                                       <?php } }?>
                                    </ul>
                                 </div>
                              </td>
                              <td><?=$row->created_at?></td>
                              <td>
                                 <?php if($row->is_send){?>
                                    <span class="badge bg-success">YES</span>
                                    <p><?=date_format(date_create($row->updated_at), "M d, Y h:i A")?></p>
                                 <?php } else {?>
                                    <span class="badge bg-danger">NO</span>
                                 <?php }?>
                              </td>
                              <td>
                                 <?php if(!$value->is_send){?>
                                    <a href="<?=url('admin/' . $controllerRoute . '/edit/'.Helper::encoded($value->id))?>" class="btn btn-outline-primary btn-sm" title="Edit <?=$module['title']?>"><i class="fa fa-edit"></i></a>
                                    <a href="<?=url('admin/' . $controllerRoute . '/send/'.Helper::encoded($value->id))?>" class="btn btn-outline-info btn-sm" title="Send" onclick="return confirm('Do you want to send this notifications ?');"><i class="fa fa-envelope"></i></a>
                                    <?php if($value->status){?>
                                    <a href="<?=url('admin/' . $controllerRoute . '/change-status/'.Helper::encoded($value->id))?>" class="btn btn-outline-success btn-sm" title="Activate <?=$module['title']?>"><i class="fa fa-check"></i></a>
                                    <?php } else {?>
                                    <a href="<?=url('admin/' . $controllerRoute . '/change-status/'.Helper::encoded($value->id))?>" class="btn btn-outline-warning btn-sm" title="Deactivate <?=$module['title']?>"><i class="fa fa-times"></i></a>
                                    <?php }?>
                                 <?php }?>
                                 <a href="<?=url('admin/' . $controllerRoute . '/delete/'.Helper::encoded($value->id))?>" class="btn btn-outline-danger btn-sm" title="Delete <?=$module['title']?>" onclick="return confirm('Do You Want To Delete This <?=$module['title']?>');"><i class="fa fa-trash"></i></a>
                              </td>
                           </tr>
                     <?php } } else {?>
                           <tr>
                              <td colspan="7" style="color:red; text-align:center;">No records found</td>
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