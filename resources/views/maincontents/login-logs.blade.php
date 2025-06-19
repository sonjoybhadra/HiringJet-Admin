<?php
use App\Helpers\Helper;
$user_type = session('type');
?>
@extends('layouts.main')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
   <div class="row g-6">
      <h4><?=$page_header?></h4>
      <h6 class="py-3 breadcrumb-wrapper mb-4">
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
         <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
           <li class="nav-item">
             <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-1" aria-controls="navs-pills-justified-profile" aria-selected="true">Success Login</button>
           </li>
           <li class="nav-item">
             <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-2" aria-controls="navs-pills-justified-general" aria-selected="false">Failed Login</button>
           </li>
           <li class="nav-item">
             <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-0" aria-controls="navs-pills-justified-password" aria-selected="false">Log Out</button>
           </li>
         </ul>
         <div class="tab-content">
            <div class="tab-pane fade show active" id="navs-pills-justified-1" role="tabpanel">
               <h5>Success Login</h5>
               <div class="card mb-4">
                  <div class="card-body">
                     <div id="table-overlay-loader" class="text-loader">
                        Fetching data. Please wait <span id="dot-animation">.</span>
                     </div>
                     @include('components.table', [
                        'containerId' => 'table1',
                        'searchId' => 'search1',
                        'table' => 'user_activities',
                        'columns' => ['user_type', 'user_name', 'user_email', 'ip_address', 'activity_details', 'created_at', 'platform_type'],
                        'visibleColumns' => ['user_type', 'user_name', 'user_email', 'ip_address', 'activity_details', 'created_at', 'platform_type'],
                        'headers' => ['#', 'User Type', 'Name', 'Email', 'IP Address', 'Activity Details', 'Activity Date', 'Platform'],
                        'filename' => "Success_Login_Logs",
                        'orderBy' => 'id',
                        'orderType' => 'desc',
                        'conditions' => [
                           ['column' => 'activity_type', 'operator' => '=', 'value' => 1]
                        ],
                        'routePrefix' => 'login-logs',
                        'showActions' => false, // set to false to hide actions
                        'statusColumn' => 'activity_type' // optional, defaults to 'is_active'
                     ])
                  </div>
               </div>
            </div>
            <div class="tab-pane fade" id="navs-pills-justified-2" role="tabpanel">
               <h5>Failed Login</h5>
               <div class="card mb-4">
                  <div class="card-body">
                     <div id="table-overlay-loader" class="text-loader">
                        Fetching data. Please wait <span id="dot-animation">.</span>
                     </div>
                     @include('components.table', [
                        'containerId' => 'table2',
                        'searchId' => 'search2',
                        'table' => 'user_activities',
                        'columns' => ['user_type', 'user_name', 'user_email', 'ip_address', 'activity_details', 'created_at', 'platform_type'],
                        'visibleColumns' => ['user_type', 'user_name', 'user_email', 'ip_address', 'activity_details', 'created_at', 'platform_type'],
                        'headers' => ['#', 'User Type', 'Name', 'Email', 'IP Address', 'Activity Details', 'Activity Date', 'Platform'],
                        'filename' => "Failed_Login_Logs",
                        'orderBy' => 'id',
                        'orderType' => 'desc',
                        'conditions' => [
                           ['column' => 'activity_type', 'operator' => '=', 'value' => 0]
                        ],
                        'routePrefix' => 'login-logs',
                        'showActions' => false, // set to false to hide actions
                        'statusColumn' => 'activity_type' // optional, defaults to 'is_active'
                     ])
                  </div>
               </div>
            </div>
            <div class="tab-pane fade" id="navs-pills-justified-0" role="tabpanel">
               <h5>Log Out</h5>
               <div class="card mb-4">
                  <div class="card-body">
                     <div id="table-overlay-loader" class="text-loader">
                        Fetching data. Please wait <span id="dot-animation">.</span>
                     </div>
                     @include('components.table', [
                        'containerId' => 'table3',
                        'searchId' => 'search3',
                        'table' => 'user_activities',
                        'columns' => ['user_type', 'user_name', 'user_email', 'ip_address', 'activity_details', 'created_at', 'platform_type'],
                        'visibleColumns' => ['user_type', 'user_name', 'user_email', 'ip_address', 'activity_details', 'created_at', 'platform_type'],
                        'headers' => ['#', 'User Type', 'Name', 'Email', 'IP Address', 'Activity Details', 'Activity Date', 'Platform'],
                        'filename' => "Logout_Logs",
                        'orderBy' => 'id',
                        'orderType' => 'desc',
                        'conditions' => [
                           ['column' => 'activity_type', 'operator' => '=', 'value' => 2]
                        ],
                        'routePrefix' => 'login-logs',
                        'showActions' => false, // set to false to hide actions
                        'statusColumn' => 'activity_type' // optional, defaults to 'is_active'
                     ])
                  </div>
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