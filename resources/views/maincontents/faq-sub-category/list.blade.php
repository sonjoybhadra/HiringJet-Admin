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
               <div id="table-overlay-loader" class="text-loader">
                  Fetching data. Please wait <span id="dot-animation">.</span>
               </div>
                @include('components.table', [
                  'containerId' => 'table1',
                  'searchId' => 'search1',
                  'table' => 'faq_sub_categories',
                  'columns' => ['faq_category_id', 'name', 'description', 'image', 'created_at', 'faq_sub_categories.status'],
                  'visibleColumns' => ['faq_category_name', 'name', 'description', 'image', 'created_at'],
                  'headers' => ['#', 'FAQ Category', 'Name', 'Description', 'Image', 'Created At'],
                  'filename' => "FAQ_Sub_Category",
                  'orderBy' => 'id',
                  'orderType' => 'desc',
                  'conditions' => [
                    ['column' => 'faq_sub_categories.status', 'operator' => '!=', 'value' => 3]
                  ],
                  'routePrefix' => 'faq-sub-category',
                  'showActions' => true, // set to false to hide actions
                  'statusColumn' => 'status', // optional, defaults to 'is_active',
                  'imageColumns' => ['image'],
                  'joins' => [
                     [
                        'table' => 'faq_categories',
                        'localKey' => 'faq_category_id',
                        'foreignKey' => 'id',
                        'select' => ['name as faq_category_name']
                     ]
                  ]
                ])
            </div>
        </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
<script src="<?=config('constants.admin_assets_url')?>assets/js/table.js"></script>
@endsection