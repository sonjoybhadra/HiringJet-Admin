<?php
use App\Helpers\Helper;
$user_type = session('type');
?>
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
         <div class="card mb-4">
            <div class="card-body">
                @include('components.table', [
                'containerId' => 'table1',
                'searchId' => 'search1',
                'table' => 'user_activities',
                'columns' => ['user_type', 'user_name', 'user_email', 'ip_address', 'activity_details', 'created_at', 'platform_type'],
                'visibleColumns' => ['user_type', 'user_name', 'user_email', 'ip_address', 'activity_details', 'created_at', 'platform_type'],
                'headers' => ['#', 'User Type', 'Name', 'Email', 'IP Address', 'Activity Details', 'Activity Date', 'Platform'],
                'filename' => "User_Activity_Logs",
                'orderBy' => 'id',
                'orderType' => 'desc',
                'conditions' => [
                    ['column' => 'activity_type', 'operator' => '=', 'value' => 3]
                ],
                'routePrefix' => 'user-activity-logs',
                'showActions' => false, // set to false to hide actions
                'statusColumn' => 'activity_type' // optional, defaults to 'is_active'
                ])
            </div>
        </div>
      </div>
   </div>
</div>
<script src="<?=env('ADMIN_ASSETS_URL')?>assets/js/table.js"></script>