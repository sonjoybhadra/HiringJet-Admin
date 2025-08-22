<?php

use App\Models\Country;
use App\Models\City;
use App\Models\Industry;
use App\Models\Designation;
use App\Models\Employer;
use App\Models\PostJob;
use App\Models\UserEmployer;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$controllerRoute = $module['controller_route'];
?>
<style>
    .theme-color-text{
        color: #092b61;
    }
</style>
@extends('layouts.main')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6">
        <h4><?= $page_header ?></h4>
        <h6 class="breadcrumb-wrapper">
            <span class="text-muted fw-light"><a href="<?= url('dashboard') ?>">Dashboard</a> /</span> <?= $page_header ?>
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
                <div class="card-body">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Header -->
                        <div class="row">
                            <div class="col-12">
                                <?php if ($row) { ?>
                                    <?php
                                    $getCountry = Country::select('name')->where('id', $row->country_id)->first();
                                    $getCity = City::select('name')->where('id', $row->city_id)->first();
                                    ?>
                                    <div class="card mb-6">
                                        <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-3">
                                            <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                                                <?php if ($row->logo == null) { ?>
                                                    <img src="{{ config('constants.admin_assets_url') }}assets/img/avatars/no-image.jpg" alt="<?= $row->first_name . ' ' . $row->last_name ?>" class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" style="width: 100px;height: 100px;" />
                                                <?php } else { ?>
                                                    <img src="<?= url('/') . '/' . $row->logo ?>" alt="<?= $row->first_name . ' ' . $row->last_name ?>" class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" style="width: 100px;" />
                                                <?php } ?>
                                            </div>
                                            <div class="flex-grow-1 mt-3 mt-lg-5">
                                                <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                                                    <div class="user-profile-info">
                                                        <h4 class="mb-2 mt-lg-6"><?= $row->first_name . ' ' . $row->last_name ?></h4>

                                                        <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                                                            <li class="list-inline-item d-flex gap-2 align-items-center">
                                                                <i class="ti ti-map-pin ti-lg"></i><span class="fw-medium"><?= (($getCity) ? $getCity->name : '') ?>, <?= (($getCountry) ? $getCountry->name : '') ?></span>
                                                            </li>
                                                            <li class="list-inline-item d-flex gap-2 align-items-center">
                                                                <i class="ti ti-calendar ti-lg"></i><span class="fw-medium"> Joined <?= date_format(date_create($row->created_at), "F Y") ?></span>
                                                            </li>
                                                        </ul>

                                                        <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                                                            <li class="list-inline-item d-flex gap-2 align-items-center">
                                                                <i class="ti ti-phone ti-lg"></i><span class="fw-medium"> <?= $row->country_code ?> - <?= $row->phone ?></span>
                                                            </li>
                                                            <li class="list-inline-item d-flex gap-2 align-items-center">
                                                                <i class="ti ti-envelope ti-lg"></i><span class="fw-medium"> <?= $row->email ?></span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
                                            <li class="nav-item">
                                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-1" aria-controls="navs-pills-justified-profile" aria-selected="true">Users</button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-2" aria-controls="navs-pills-justified-general" aria-selected="false">Brands</button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-3" aria-controls="navs-pills-justified-password" aria-selected="false">Folders</button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-4" aria-controls="navs-pills-justified-password" aria-selected="false">Tags</button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-5" aria-controls="navs-pills-justified-password" aria-selected="false">Saved Searches</button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-6" aria-controls="navs-pills-justified-password" aria-selected="false">Email Templates</button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-7" aria-controls="navs-pills-justified-password" aria-selected="false">Jobs</button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-8" aria-controls="navs-pills-justified-password" aria-selected="false">CVs</button>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="navs-pills-justified-1" role="tabpanel">
                                                <h5>Users</h5>
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Sl No.</th>
                                                            <th>Name</th>
                                                            <th>Email</th>
                                                            <th>Phone</th>
                                                            <th>Designation</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $sl_no=1;
                                                        if($subusers){ foreach($subusers as $subuser){
                                                        ?>
                                                            <tr>
                                                                <td><?=$sl_no++?></td>
                                                                <td><?=$subuser->first_name.' '.$subuser->last_name?></td>
                                                                <td><?=$subuser->email?></td>
                                                                <td><?=$subuser->country_code?> <?=$subuser->phone?></td>
                                                                <td><?=$subuser->designation_name?></td>
                                                            </tr>
                                                        <?php } } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="tab-pane fade" id="navs-pills-justified-2" role="tabpanel">
                                                <h5>Brands</h5>
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Sl No.</th>
                                                            <th>Brand Logo</th>
                                                            <th>Brand Name</th>
                                                            <th>Industry</th>
                                                            <th>Contact Person</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $sl_no=1;
                                                        if($brands){ foreach($brands as $brand){
                                                        ?>
                                                            <tr>
                                                                <td><?=$sl_no++?></td>
                                                                <td><img src="<?=url('/') . '/' . $brand->company_logo?>" alt="<?=$brand->company_name?>"></td>
                                                                <td><?=$brand->company_name?></td>
                                                                <td><?=$brand->industry_name?></td>
                                                                <td><?=$brand->contact_person_id?></td>
                                                            </tr>
                                                        <?php } } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="tab-pane fade" id="navs-pills-justified-3" role="tabpanel">
                                                <h5>Folders</h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="theme-color-text">My CV Folders (15)</h6>
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sl No.</th>
                                                                    <th>Name of the folder</th>
                                                                    <th>No. of CVs</th>
                                                                    <th>Last Modified Date</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>1</td>
                                                                    <td>HR DEMO</td>
                                                                    <td>75</td>
                                                                    <td>10 Sept 2024</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="theme-color-text">Folders of other Users (19)</h6>
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sl No.</th>
                                                                    <th>Name of the folder</th>
                                                                    <th>No. of CVs</th>
                                                                    <th>Last Modified Date</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>1</td>
                                                                    <td>HR DEMO</td>
                                                                    <td>75</td>
                                                                    <td>10 Sept 2024</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="navs-pills-justified-4" role="tabpanel">
                                                <h5>Tags</h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="theme-color-text">My Tag (15)</h6>
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sl No.</th>
                                                                    <th>Name of the tag</th>
                                                                    <th>Last Modified Date</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>1</td>
                                                                    <td>Backend Developer</td>
                                                                    <td>10 Sept 2024</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="theme-color-text">Tags of other Users (19)</h6>
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sl No.</th>
                                                                    <th>Name of the tag</th>
                                                                    <th>Last Modified Date</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>1</td>
                                                                    <td>Backend Developer</td>
                                                                    <td>10 Sept 2024</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="navs-pills-justified-5" role="tabpanel">
                                                <h5>Saved Searches</h5>
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Sl No.</th>
                                                            <th>IP Address</th>
                                                            <th>Search String</th>
                                                            <th>Search Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>1</td>
                                                            <td>202.8.119.226</td>
                                                            <td>{"country":[],"city":[],"industry":[],"nationality":[],"designation":[],"employer":[],"freshness":null,"gender":null,"experience":null,"salary_range":null,"title":null,"location":null,"keyword":null,"job_category":null,"job_type":"on-site-jobs","page":1}</td>
                                                            <td>14 Aug 2025</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="tab-pane fade" id="navs-pills-justified-6" role="tabpanel">
                                                <h5>Email Templates</h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="theme-color-text">My Templates (8)</h6>
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sl No.</th>
                                                                    <th>Name of the Template</th>
                                                                    <th>Last Modified Date</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>1</td>
                                                                    <td>HR DEMO</td>
                                                                    <td>10 Sept 2024</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="theme-color-text">Templates of other Users (19)</h6>
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sl No.</th>
                                                                    <th>Name of the Template</th>
                                                                    <th>Last Modified Date</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>1</td>
                                                                    <td>HR DEMO</td>
                                                                    <td>10 Sept 2024</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="navs-pills-justified-7" role="tabpanel">
                                                <h5>Jobs</h5>
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Sl No.</th>
                                                            <th>Job No.</th>
                                                            <th>Position Name</th>
                                                            <th>Job Type</th>
                                                            <th>Posting Open Date</th>
                                                            <th>Posting Close Date</th>
                                                            <th>Created At</th>
                                                            <!-- <th>Created By</th> -->
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $postedJobs = PostJob::select('id', 'job_no', 'position_name', 'job_type', 'posting_open_date', 'posting_close_date', 'created_at', 'created_by')->where('employer_id', '=', $business_id)->where('status', '=', 1)->orderBy('id', 'DESC')->get();
                                                        $sl_no=1;
                                                        if($postedJobs){ foreach($postedJobs as $postedJob){
                                                        ?>
                                                            <tr>
                                                                <td><?=$sl_no++?></td>
                                                                <td><?=$postedJob->job_no?></td>
                                                                <td><?=$postedJob->position_name?></td>
                                                                <td><?=$postedJob->job_type?></td>
                                                                <td><?=(($postedJob->posting_open_date != '')?date_format(date_create($postedJob->posting_open_date), "Y-m-d"):'')?></td>
                                                                <td><?=(($postedJob->posting_close_date != '')?date_format(date_create($postedJob->posting_close_date), "Y-m-d"):'')?></td>
                                                                <td><?=$postedJob->created_at?></td>
                                                                <!-- <td><?=$postedJob->created_by?></td> -->
                                                            </tr>
                                                        <?php } }?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="tab-pane fade" id="navs-pills-justified-8" role="tabpanel">
                                                <h5>CVs</h5>
                                                
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
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