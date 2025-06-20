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
            <div class="card-body">
               <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <!-- Header -->
              <div class="row">
                <div class="col-12">
                  <div class="card mb-6">
                    <!-- <div class="user-profile-header-banner">
                      <img src="{{ config('constants.admin_assets_url') }}assets/img/pages/profile-banner.png" alt="Banner image" class="rounded-top" />
                    </div> -->
                    <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-5">
                      <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                        <img
                          src="{{ config('constants.admin_assets_url') }}assets/img/avatars/1.png"
                          alt="user image"
                          class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" />
                      </div>
                      <div class="flex-grow-1 mt-3 mt-lg-5">
                        <div
                          class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                          <div class="user-profile-info">
                            <h4 class="mb-2 mt-lg-6">John Doe</h4>
                            <ul
                              class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                              <li class="list-inline-item d-flex gap-2 align-items-center">
                                <i class="ti ti-palette ti-lg"></i><span class="fw-medium">UX Designer</span>
                              </li>
                              <li class="list-inline-item d-flex gap-2 align-items-center">
                                <i class="ti ti-map-pin ti-lg"></i><span class="fw-medium">Vatican City</span>
                              </li>
                              <li class="list-inline-item d-flex gap-2 align-items-center">
                                <i class="ti ti-calendar ti-lg"></i><span class="fw-medium"> Joined April 2021</span>
                              </li>
                            </ul>
                          </div>
                          <!-- <a href="javascript:void(0)" class="btn btn-primary mb-1">
                            <i class="ti ti-user-check ti-xs me-2"></i>Connected
                          </a> -->
                        </div>
                      </div>
                    </div>

                    <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-1" aria-controls="navs-pills-justified-profile" aria-selected="true">Professional Info</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-2" aria-controls="navs-pills-justified-general" aria-selected="false">Personal Info</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-3" aria-controls="navs-pills-justified-password" aria-selected="false">Educational Info</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-4" aria-controls="navs-pills-justified-password" aria-selected="false">Employment Info</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-5" aria-controls="navs-pills-justified-password" aria-selected="false">Accomplishments</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-6" aria-controls="navs-pills-justified-password" aria-selected="false">Desired Jobs</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-7" aria-controls="navs-pills-justified-password" aria-selected="false">CV</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-8" aria-controls="navs-pills-justified-password" aria-selected="false">Applied Jobs</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-9" aria-controls="navs-pills-justified-password" aria-selected="false">Shortlisted Jobs</button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="navs-pills-justified-1" role="tabpanel">
                            <h5>Professional Info</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-2" role="tabpanel">
                            <h5>Personal Info</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-3" role="tabpanel">
                            <h5>Educational Info</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-4" role="tabpanel">
                            <h5>Employment Info</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-5" role="tabpanel">
                            <h5>Accomplishments</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-6" role="tabpanel">
                            <h5>Desired Jobs</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-7" role="tabpanel">
                            <h5>CV</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-8" role="tabpanel">
                            <h5>Applied Jobs</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-9" role="tabpanel">
                            <h5>Shortlisted Jobs</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                
                                </div>
                            </div>
                        </div>
                    </div>

                  </div>
                </div>
              </div>
              <!--/ Header -->

              
            </div>
            <!-- / Content -->
            </div>
        </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
<script src="<?=config('constants.admin_assets_url')?>assets/js/table.js"></script>
@endsection