<?php

use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
@extends('layouts.main')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
   <div class="row g-6">
      <h4><?= $page_header ?></h4>
      <h6 class="breadcrumb-wrapper">
         <span class="text-muted fw-light"><a href="<?= url('dashboard') ?>">Dashboard</a> /</span>
         <?= $page_header ?>
      </h6>
      <div class="nav-align-top mb-4">
         <?php if (session('success_message')) { ?>
            <div class="alert alert-success alert-dismissible autohide" role="alert">
               <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-desktop align-top me-2"></i>Success!</h6>
               <span><?= session('success_message') ?></span>
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
               </button>
            </div>
         <?php } ?>
         <?php if (session('error_message')) { ?>
            <div class="alert alert-danger alert-dismissible autohide" role="alert">
               <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-store align-top me-2"></i>Error!</h6>
               <span><?= session('error_message') ?></span>
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
               </button>
            </div>
         <?php } ?>
         <div class="card mb-4">
            <div class="card-header">
               <a href="<?= url($controllerRoute . '/add/') ?>" class="btn btn-outline-success btn-sm float-end">Add <?= $module['title'] ?></a>
            </div>
            <div class="card-body">
               <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
                  <li class="nav-item">
                     <a href="<?= url('/job/verified-employer-pending-job') ?>" class="nav-link active" role="tab">
                        <i class="tf-icons bx bx-home me-1"></i> Verified Employer Pending Jobs
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="<?= url('/job/non-verified-employer-pending-job') ?>" class="nav-link" role="tab">
                        <i class="tf-icons bx bx-user me-1"></i> Non-Verified Employer Pending Jobs
                     </a>
                  </li>
                  <li class="nav-item">
                     <a href="<?= url('/job/internal-employer-pending-job') ?>" class="nav-link" role="tab">
                        <i class="tf-icons bx bx-lock me-1"></i> Internal Employer Pending Jobs
                     </a>
                  </li>
               </ul>
               <div class="tab-content">
                  <div id="table-overlay-loader" class="text-loader">
                     Fetching data. Please wait <span id="dot-animation">.</span>
                  </div>
                  @include('components.table', [
                  'containerId' => 'table1',
                  'searchId' => 'search1',
                  'table' => 'post_jobs',
                  'columns' => ['job_no', 'employer_id', 'position_name', 'job_type', 'posting_open_date', 'posting_close_date', 'created_at', 'created_by', 'status'],
                  'visibleColumns' => ['job_no', 'employer_name', 'position_name', 'job_type', 'posting_open_date', 'posting_close_date', 'created_at', 'created_by_name'],
                  'headers' => ['#', 'Job No.', 'Employer Name', 'Position Name', 'Job Type', 'Posting Open Date', 'Posting Close Date', 'Created At', 'Created By'],
                  'filename' => "VerifiedEmployerPendingJob",
                  'orderBy' => 'id',
                  'orderType' => 'desc',
                  'conditions' => [
                  ['column' => 'post_jobs.status', 'operator' => '=', 'value' => 0],
                  ['column' => 'employers.status', 'operator' => '=', 'value' => 4],
                  ],
                  'joins' => [
                  [
                  'table' => 'users',
                  'localKey' => 'created_by',
                  'foreignKey' => 'id',
                  'select' => ['first_name as created_by_name']
                  ]
                  ],
                  'routePrefix' => 'job',
                  'showActions' => true,
                  'statusColumn' => 'status'
                  ])
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection

@section('scripts')
<script src="<?= config('constants.admin_assets_url') ?>assets/js/table.js"></script>
<script>
   // Refresh page every 60 seconds
   setInterval(function() {
      window.location.reload();
   }, 60000); // 60000ms = 60s
</script>
@endsection