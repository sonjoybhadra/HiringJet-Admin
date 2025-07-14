<?php
use App\Models\Country;
use App\Models\City;
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
?>
@extends('layouts.main')
@section('content')
<style>
  .mr-5{
    margin-right: 5px;
  }
</style>
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
                  <?php if($row){?>
                    <?php
                    $getCountry = Country::select('name')->where('id', $row->country_id)->first();
                    $getCity = City::select('name')->where('id', $row->city_id)->first();
                    ?>
                  <div class="card mb-6">
                    <!-- <div class="user-profile-header-banner">
                      <img src="{{ config('constants.admin_assets_url') }}assets/img/pages/profile-banner.png" alt="Banner image" class="rounded-top" />
                    </div> -->
                    <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-3">
                      <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                        <?php if($row->profile_image == null){?>
                          <img src="{{ config('constants.admin_assets_url') }}assets/img/avatars/1.png" alt="<?=$row->first_name.' '.$row->last_name?>" class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" />
                        <?php } else {?>
                          <img src="<?=url('/').'/'.$row->profile_image?>" alt="<?=$row->first_name.' '.$row->last_name?>" class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" style="width: 100px;" />
                        <?php }?>
                      </div>
                      <div class="flex-grow-1 mt-3 mt-lg-5">
                        <div
                          class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                          <div class="user-profile-info">
                            <h4 class="mb-2 mt-lg-6"><?=$row->first_name.' '.$row->last_name?></h4>
                            <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                              <li class="list-inline-item d-flex gap-2 align-items-center">
                                <i class="ti ti-palette ti-lg"></i><span class="fw-medium"><?=$row->resume_headline?></span>
                              </li>
                              <li class="list-inline-item d-flex gap-2 align-items-center">
                                <i class="ti ti-map-pin ti-lg"></i><span class="fw-medium"><?=(($getCity)?$getCity->name:'')?>, <?=(($getCountry)?$getCountry->name:'')?></span>
                              </li>
                              <li class="list-inline-item d-flex gap-2 align-items-center">
                                <i class="ti ti-calendar ti-lg"></i><span class="fw-medium"> Joined <?=date_format(date_create($row->created_at), "F Y")?></span>
                              </li>
                            </ul>
                            <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                              <li class="list-inline-item d-flex gap-2 align-items-center">
                                <i class="ti ti-phone ti-lg"></i><span class="fw-medium"> <?=$row->country_code?> - <?=$row->phone?></span>
                                <?php if($row->phone_verified_at != null){?>
                                  <small class="text-success" style="font-size:10px;"><i class="fa fa-check-circle"></i> Verified</small>
                                <?php } else {?>
                                  <small class="text-danger" style="font-size:10px;"><i class="fa fa-times-circle"></i> Not Verified</small>
                                <?php } ?>
                              </li>
                              <li class="list-inline-item d-flex gap-2 align-items-center">
                                <i class="ti ti-envelope ti-lg"></i><span class="fw-medium"> <?=$row->email?></span>
                                <?php if($row->email_verified_at != null){?>
                                  <small class="text-success" style="font-size:10px;"><i class="fa fa-check-circle"></i> Verified</small>
                                <?php } else {?>
                                  <small class="text-danger" style="font-size:10px;"><i class="fa fa-times-circle"></i> Not Verified</small>
                                <?php } ?>
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
                                <h5>CV Headline</h5>
                                <span><?=$row->resume_headline?></span>
                              </div>
                            </div>
                            <div class="card mb-4">
                              <div class="card-body">
                                <h5>Keyskills</h5>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Accouting And Auditing</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Accouting</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Accouting tally</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Accoutins</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Accouting & Finance</button>
                              </div>
                            </div>
                            <div class="card mb-4">
                              <div class="card-body">
                                <h5>Professional Details</h5>
                                <div class="row">
                                  <div class="col-md-4 mb-3">
                                    <h6>Total Work Experience</h6>
                                    <span>5 years, 5 months</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Industry</h6>
                                    <span>Accounting & Auditing</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Functional Area</h6>
                                    <span>Accounts / Taxation</span>
                                  </div>

                                  <div class="col-md-4 mb-3">
                                    <h6>Current Work Level</h6>
                                    <span>Entry Level</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Monthly Salary</h6>
                                    <span>INR 200000</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Additional Perks & Benefits</h6>
                                    <span>Performance Bonus</span>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="card mb-4">
                              <div class="card-body">
                                <h5>Profile Summary</h5>
                                <p><?=$row->profile_summery?></p>
                              </div>
                            </div>
                            <div class="card mb-4">
                              <div class="card-body">
                                <h5>IT Skills</h5>
                                <div class="row">
                                  <div class="col-md-6">
                                    <div class="card">
                                      <h5 class="card-header">Microsoft Word</h5>
                                      <div class="card-body">
                                        <small class="text-muted">Version: 2013</small><br>
                                        <small class="text-muted">Experience: 5 years</small><br>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-md-6">
                                    <div class="card">
                                      <h5 class="card-header">Microsoft Excel</h5>
                                      <div class="card-body">
                                        <small class="text-muted">Version: 2013</small><br>
                                        <small class="text-muted">Experience: 3 years 6 months</small><br>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-2" role="tabpanel">
                          <h5>Personal Info</h5>
                          <div class="card mb-4">
                              <div class="card-body">
                                <div class="row">
                                  <div class="col-md-4 mb-3">
                                    <h6>Date of Birth</h6>
                                    <span>21/02/1989</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Gender</h6>
                                    <span>Male</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Marital Status</h6>
                                    <span>Single</span>
                                  </div>
                                  <hr>

                                  <div class="col-md-4 mb-3">
                                    <h6>Nationality</h6>
                                    <span>INDIAN</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Language Known</h6>
                                    <span>English (Intermediate), Hindi (Expert)</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Category</h6>
                                    <span>General/UR</span>
                                  </div>
                                  <hr>

                                  <div class="col-md-4 mb-3">
                                    <h6>Religion</h6>
                                    <span>Islam</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Country</h6>
                                    <span>INDIA</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Current Location</h6>
                                    <span>Kolkata</span>
                                  </div>
                                  <hr>

                                  <div class="col-md-4 mb-3">
                                    <h6>Career Break</h6>
                                    <span>No</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Differently Abled</h6>
                                    <span>No</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>USA Work Permit</h6>
                                    <span>No</span>
                                  </div>
                                  <hr>

                                  <div class="col-md-4 mb-3">
                                    <h6>Other Countries Work Permit</h6>
                                    <span>Pk, BAN, SL</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Driving License</h6>
                                    <span>No</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Alternative Email Address</h6>
                                    <span>test@yopmail.com</span>
                                  </div>
                                  <hr>

                                  <div class="col-md-4 mb-3">
                                    <h6>Alternative Phone</h6>
                                    <span>+91 1234567890</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Permanent Address</h6>
                                    <span>112 north road, Kolkata, India</span>
                                  </div>
                                  <div class="col-md-4 mb-3">
                                    <h6>Pincode</h6>
                                    <span>700005</span>
                                  </div>
                                  <hr>

                                  <div class="col-md-4 mb-3">
                                    <h6>Diverse Background</h6>
                                    <span>Not specified</span>
                                  </div>
                                </div>
                              </div>
                          </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-3" role="tabpanel">
                          <h5>Educational Info</h5>
                          <div class="card mb-4">
                              <div class="card-body">
                                <div class="list-group">
                                  <div class="list-group-item list-group-item-action d-flex align-items-center cursor-pointer">
                                    <span class="rounded-circle me-3" width="40"><i class="fa fa-briefcase fa-2x text-primary" style="border: 1px solid #092b61;padding: 15px;border-radius: 50%;"></i></span>
                                    <div class="w-100">
                                      <div class="d-flex justify-content-between">
                                        <div class="user-info">
                                          <h5 class="mb-1 fw-normal">Doctorate - MPhil</h5>
                                          <span class="text-primary">Ace IT Training Dubai (2018-2020)</span><br>
                                          <small class="text-muted">Specialization: Achitecture</small><br>
                                          <small class="text-muted">Course Type: Full time</small><br>
                                          <small class="text-muted">Grade: 7.4</small><br>
                                          <small class="text-muted">Location: Dubai</small>
                                        </div>
                                        <!-- <div class="add-btn">
                                          <button class="btn btn-primary btn-sm">Add</button>
                                        </div> -->
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                          </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-4" role="tabpanel">
                          <h5>Employment Info</h5>
                          <div class="card mb-4">
                              <div class="card-body">
                                <div class="demo-inline-spacing mt-4">
                                  <div class="list-group">
                                    <div class="list-group-item list-group-item-action d-flex align-items-center cursor-pointer">
                                      <span class="rounded-circle me-3" width="40"><i class="fa fa-briefcase fa-2x text-primary" style="border: 1px solid #092b61;padding: 15px;border-radius: 50%;"></i></span>
                                      <div class="w-100">
                                        <div class="d-flex justify-content-between">
                                          <div class="user-info">
                                            <h5 class="mb-1 fw-normal">Account / Group Of Factories</h5>
                                            <span class="text-primary">Abu Dhabi (January 2020 - Present)</span><br>
                                            <small class="text-muted">Experiences: 5 years, 5 months</small><br>
                                            <small class="text-muted">Type: Full time</small><br>
                                            <small class="text-muted">Salry: INR 2000000</small><br>
                                            <small class="text-muted">Notice Period: 1 month</small><br>
                                            <small class="text-muted">Skills: Accounting</small>
                                          </div>
                                          <!-- <div class="add-btn">
                                            <button class="btn btn-primary btn-sm">Add</button>
                                          </div> -->
                                        </div>
                                      </div>
                                    </div>
                                    <div class="list-group-item list-group-item-action d-flex align-items-center cursor-pointer">
                                      <span class="rounded-circle me-3" width="40"><i class="fa fa-briefcase fa-2x text-primary" style="border: 1px solid #092b61;padding: 15px;border-radius: 50%;"></i></span>
                                      <div class="w-100">
                                        <div class="d-flex justify-content-between">
                                          <div class="user-info">
                                            <h5 class="mb-1 fw-normal">Account / Group Of Factories</h5>
                                            <span class="text-primary">Abu Dhabi (January 2020 - Present)</span><br>
                                            <small class="text-muted">Experiences: 5 years, 5 months</small><br>
                                            <small class="text-muted">Type: Full time</small><br>
                                            <small class="text-muted">Salry: INR 2000000</small><br>
                                            <small class="text-muted">Notice Period: 1 month</small><br>
                                            <small class="text-muted">Skills: Accounting</small>
                                          </div>
                                          <!-- <div class="add-btn">
                                            <button class="btn btn-primary btn-sm">Add</button>
                                          </div> -->
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                          </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-5" role="tabpanel">
                            <h5>Accomplishments</h5>
                            <div class="card mb-4">
                              <div class="card-body">
                                <div class="card mb-4">
                                  <div class="card-body">
                                    <h5>Certification</h5>
                                    <div class="row">
                                      <div class="col-md-12">
                                        <div class="card">
                                          <div class="row g-0">
                                            <div class="col-md-1">
                                              <img class="card-img card-img-left" src="https://static.vecteezy.com/system/resources/previews/006/414/199/non_2x/globe-icon-internet-web-symbol-isolated-on-white-background-free-vector.jpg" alt="Card image" style="width:75px; height:75px;">
                                            </div>
                                            <div class="col-md-11">
                                              <div class="card-body">
                                                <h5 class="card-title">AAA</h5>
                                                <small class="text-muted">Lorem ipsum</small><br>
                                                <small class="text-muted">January 2022 - December 2030</small>
                                                <p class="card-text">
                                                  <small class="text-body-secondary">
                                                    <a href="javascript:void(0);" target="_blank" class="text-primary"><i class="fa fa-paper-clip"></i> View Certificate Online</a>
                                                  </small>
                                                </p>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="card mb-4">
                                  <div class="card-body">
                                    <h5>Online Profile</h5>
                                    <div class="row">
                                      <div class="col-md-12">
                                        <div class="card">
                                          <div class="row g-0">
                                            <div class="col-md-1">
                                              <img class="card-img card-img-left" src="https://static.vecteezy.com/system/resources/previews/006/414/199/non_2x/globe-icon-internet-web-symbol-isolated-on-white-background-free-vector.jpg" alt="Card image" style="width:75px; height:75px;">
                                            </div>
                                            <div class="col-md-11">
                                              <div class="card-body">
                                                <h5 class="card-title">Personal Website</h5>
                                                <p class="card-text">
                                                  <small class="text-body-secondary">
                                                    <a href="javascript:void(0);" target="_blank" class="text-primary"><i class="fa fa-paper-clip"></i> https://www.google.com</a>
                                                  </small>
                                                </p>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="card">
                                          <div class="row g-0">
                                            <div class="col-md-1">
                                              <img class="card-img card-img-left" src="https://static.vecteezy.com/system/resources/previews/006/414/199/non_2x/globe-icon-internet-web-symbol-isolated-on-white-background-free-vector.jpg" alt="Card image" style="width:75px; height:75px;">
                                            </div>
                                            <div class="col-md-11">
                                              <div class="card-body">
                                                <h5 class="card-title">Linkedin</h5>
                                                <p class="card-text">
                                                  <small class="text-body-secondary">
                                                    <a href="javascript:void(0);" target="_blank" class="text-primary"><i class="fa fa-paper-clip"></i> https://www.google.com</a>
                                                  </small>
                                                </p>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="card">
                                          <div class="row g-0">
                                            <div class="col-md-1">
                                              <img class="card-img card-img-left" src="https://static.vecteezy.com/system/resources/previews/006/414/199/non_2x/globe-icon-internet-web-symbol-isolated-on-white-background-free-vector.jpg" alt="Card image" style="width:75px; height:75px;">
                                            </div>
                                            <div class="col-md-11">
                                              <div class="card-body">
                                                <h5 class="card-title">Twitter/X</h5>
                                                <p class="card-text">
                                                  <small class="text-body-secondary">
                                                    <a href="javascript:void(0);" target="_blank" class="text-primary"><i class="fa fa-paper-clip"></i> https://www.google.com</a>
                                                  </small>
                                                </p>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="card">
                                          <div class="row g-0">
                                            <div class="col-md-1">
                                              <img class="card-img card-img-left" src="https://static.vecteezy.com/system/resources/previews/006/414/199/non_2x/globe-icon-internet-web-symbol-isolated-on-white-background-free-vector.jpg" alt="Card image" style="width:75px; height:75px;">
                                            </div>
                                            <div class="col-md-11">
                                              <div class="card-body">
                                                <h5 class="card-title">Youtube</h5>
                                                <p class="card-text">
                                                  <small class="text-body-secondary">
                                                    <a href="javascript:void(0);" target="_blank" class="text-primary"><i class="fa fa-paper-clip"></i> https://www.google.com</a>
                                                  </small>
                                                </p>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="card">
                                          <div class="row g-0">
                                            <div class="col-md-1">
                                              <img class="card-img card-img-left" src="https://static.vecteezy.com/system/resources/previews/006/414/199/non_2x/globe-icon-internet-web-symbol-isolated-on-white-background-free-vector.jpg" alt="Card image" style="width:75px; height:75px;">
                                            </div>
                                            <div class="col-md-11">
                                              <div class="card-body">
                                                <h5 class="card-title">Instagram</h5>
                                                <p class="card-text">
                                                  <small class="text-body-secondary">
                                                    <a href="javascript:void(0);" target="_blank" class="text-primary"><i class="fa fa-paper-clip"></i> https://www.google.com</a>
                                                  </small>
                                                </p>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="card mb-4">
                                  <div class="card-body">
                                    <h5>Work Sample / Project</h5>
                                    <div class="row">
                                      <div class="col-md-12">
                                        <div class="card">
                                          <div class="row g-0">
                                            <div class="col-md-1">
                                              <img class="card-img card-img-left" src="https://static.vecteezy.com/system/resources/previews/006/414/199/non_2x/globe-icon-internet-web-symbol-isolated-on-white-background-free-vector.jpg" alt="Card image" style="width:75px; height:75px;">
                                            </div>
                                            <div class="col-md-11">
                                              <div class="card-body">
                                                <h5 class="card-title">AAA</h5>
                                                <small class="text-muted">January 2022 - December 2030</small><br>
                                                <small class="text-muted">Lorem ipsum</small>
                                                <p class="card-text">
                                                  <small class="text-body-secondary">
                                                    <a href="javascript:void(0);" target="_blank" class="text-primary"><i class="fa fa-paper-clip"></i> View Work Sample Online</a>
                                                  </small>
                                                </p>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-6" role="tabpanel">
                          <h5>Desired Jobs</h5>
                          <div class="card mb-4">
                              <div class="card-body">
                                <h6 class="mt-3">Prefered Designation</h6>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Account Assistant</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Accountant And Administration</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Accountant And Auditor</button>

                                <h6 class="mt-3">Prefered Location</h6>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Delhi</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Mumbai</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Kolkata</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Bangalore</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Pune</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Hyderabad</button>

                                <h6 class="mt-3">Prefered Industry</h6>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Accouting & Auditing</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Banking / Financial Services / Broking</button>
                                <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5">Defence / Military / Government</button>

                                <h6 class="mt-3">Availability to join</h6>
                                <span>1 month</span>
                              </div>
                          </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-7" role="tabpanel">
                          <h5>CV</h5>
                          <div class="card mb-4">
                              <div class="card-body">
                                <div class="card mb-4">
                                  <div class="card-body">
                                    <h6>An updated CV increases your chances by 60% of getting job offers</h6>
                                    <div class="card">
                                      <h5 class="card-header bg-primary text-light">
                                        Md. Alam<br>
                                        Curriculum Vitae
                                      </h5>
                                      <div class="card-body">
                                        <a href="javascript:void(0);" target="_blank" class="btn btn-outline-info mt-3 mb-3">View CV</a>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                          </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-8" role="tabpanel">
                            <h5>Applied Jobs</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                  <table id="simpletable" class="table table-striped table-bordered nowrap">
                                    <thead>
                                      <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Employer</th>
                                      <th scope="col">Job Position Name</th>
                                      <th scope="col">Applied Date</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php //if(count($jobApplications)>0){ $sl=1; foreach($jobApplications as $jobApplication){?>
                                        <tr>
                                          <th scope="row">1</th>
                                          <td>Demo Company</td>
                                          <td>Senior Web Developer</td>
                                          <td>2025-06-20 10:04:53</td>
                                        </tr>
                                        <tr>
                                          <th scope="row">2</th>
                                          <td>Demo Company</td>
                                          <td>Senior Web Developer</td>
                                          <td>2025-06-20 10:04:53</td>
                                        </tr>
                                        <tr>
                                          <th scope="row">3</th>
                                          <td>Demo Company</td>
                                          <td>Senior Web Developer</td>
                                          <td>2025-06-20 10:04:53</td>
                                        </tr>
                                      <?php //} }?>
                                    </tbody>
                                  </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-justified-9" role="tabpanel">
                            <h5>Shortlisted Jobs</h5>
                            <div class="card mb-4">
                                <div class="card-body">
                                  <table id="simpletable" class="table table-striped table-bordered nowrap">
                                    <thead>
                                      <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Employer</th>
                                      <th scope="col">Job Position Name</th>
                                      <th scope="col">Shortlisted Date</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php //if(count($jobApplications)>0){ $sl=1; foreach($jobApplications as $jobApplication){?>
                                        <tr>
                                          <th scope="row">1</th>
                                          <td>Demo Company</td>
                                          <td>Senior Web Developer</td>
                                          <td>2025-06-20 10:04:53</td>
                                        </tr>
                                        <tr>
                                          <th scope="row">2</th>
                                          <td>Demo Company</td>
                                          <td>Senior Web Developer</td>
                                          <td>2025-06-20 10:04:53</td>
                                        </tr>
                                        <tr>
                                          <th scope="row">3</th>
                                          <td>Demo Company</td>
                                          <td>Senior Web Developer</td>
                                          <td>2025-06-20 10:04:53</td>
                                        </tr>
                                      <?php //} }?>
                                    </tbody>
                                  </table>
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>
                <?php }?>
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