<?php
use App\Models\Module;
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
               <div class="dt-responsive table-responsive">
                  <table id="simpletable" class="table table-striped table-bordered nowrap">
                     <thead>
                        <tr>
                           <th scope="col">#</th>
                           <th scope="col">Name</th>
                           <th scope="col">Modules</th>
                           <th scope="col">Action</th>
                        </tr>
                     </thead>
                     <tbody>
                     <?php
                     // Helper::pr($rows);
                     ?>
                     <?php if(count($rows)>0){ $sl=1; foreach($rows as $row){?>
                        <tr>
                           <th scope="row"><?=$sl++?></th>
                           <td><?=$row->role_name?></td>
                           <td>
                              <div class="row">
                                 <?php
                                 $module_id = json_decode($row->module_id);
                                 if(!empty($module_id)){ for($m=0;$m<count($module_id);$m++){
                                   $module = Module::where('id', '=', $module_id[$m])->first();
                                 ?>
                                 <div class="col-md-3 mt-3">
                                   <span class="badge bg-primary"><i class="bi bi-collection me-1"></i> <?=(($module)?$module->name:'')?></span>
                                 </div>
                                 <?php } }?>
                              </div>
                           </td>
                           <td>
                              <a href="<?=url($controllerRoute . '/edit/'.Helper::encoded($row->id))?>" class="btn btn-sm btn-primary me-1" title="Edit <?=$module['title']?>"><i class="fa fa-edit"></i></a>
                              <?php if($row->status){?>
                                 <a href="<?=url($controllerRoute . '/change-status/'.Helper::encoded($row->id))?>" class="btn btn-sm btn-success me-1" title="Activate <?=$module['title']?>"><i class="fa fa-check"></i></a>
                              <?php } else {?>
                                 <a href="<?=url($controllerRoute . '/change-status/'.Helper::encoded($row->id))?>" class="btn btn-sm btn-warning me-1" title="Deactivate <?=$module['title']?>"><i class="fa fa-times"></i></a>
                              <?php }?>
                              <a href="<?=url($controllerRoute . '/delete/'.Helper::encoded($row->id))?>" class="btn btn-sm btn-danger me-1" title="Delete <?=$module['title']?>" onclick="return confirm('Do You Want To Delete This <?=$module['title']?>');"><i class="fa fa-trash"></i>  </a>
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
</div>
@endsection
@section('scripts')
<script src="<?=config('constants.admin_assets_url')?>assets/js/table.js"></script>
@endsection