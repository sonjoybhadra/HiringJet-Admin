
<?php
use App\Helpers\Helper;
?>
@extends('layouts.main')
@section('title', 'Dashboard')
@section('content')
<style>
  .card-title h5{
    color: #092b61;
  }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row g-6">
    <h2 class="mt-3 mb-3">Welcome to <?=Helper::getSettingValue('site_name')?> admin panel</h2>
    <!-- Average Daily Sales -->
      <div class="col-xl-2 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Total Job Seekers</h5>
            <h4 class="mb-3"><?=$total_jobseeker?></h4>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Total Employers</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Total Jobs Posted</h5>
            <h4 class="mb-3"><?=$total_job_posted?></h4>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Active Job Seekers in last 30 Days</h5>
            <h4 class="mb-3"><?=$active_jobseeker_30_days?></h4>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Active Employers in last 30 Days</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Active Jobs in last 30 Days</h5>
            <h4 class="mb-3"><?=$active_jobs_30_days?></h4>
          </div>
        </div>
      </div>
    <!--/ Average Daily Sales -->

    <!--/ top 5 country -->
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0">
              <h5 class="mb-1">Top 5 Countries with most Job Seekers</h5>
            </div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_country_most_jobseeker)){ foreach($top5_country_most_jobseeker as $row){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=$row->country_name?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$row->user_count?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Countries with most Employers</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">

              <li class="mb-6 d-flex justify-content-between align-items-center">
                <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                <div class="d-flex justify-content-between w-100 flex-wrap">
                  <h6 class="mb-0 ms-4">Emails</h6>
                  <div class="d-flex">
                    <p class="ms-4 text-success mb-0">0.3%</p>
                  </div>
                </div>
              </li>
              
            </ul>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Countries with most Jobs Posted</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_country_most_job)){ foreach($top5_country_most_job as $key=>$value){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=strtoupper($key)?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$value?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
    <!--/ top 5 country -->

    <!--/ top 5 city -->
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0">
              <h5 class="mb-1">Top 5 Cities with most Job Seekers</h5>
            </div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_city_most_jobseekers)){ foreach($top5_city_most_jobseekers as $row){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=$row->city_name?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$row->user_count?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Cities with most Employers</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">

              <li class="mb-6 d-flex justify-content-between align-items-center">
                <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                <div class="d-flex justify-content-between w-100 flex-wrap">
                  <h6 class="mb-0 ms-4">Emails</h6>
                  <div class="d-flex">
                    <p class="ms-4 text-success mb-0">0.3%</p>
                  </div>
                </div>
              </li>
              
            </ul>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Cities with most Jobs Posted</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_city_most_job)){ foreach($top5_city_most_job as $key=>$value){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=strtoupper($key)?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$value?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
    <!--/ top 5 city -->

    <!--/ industry -->
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Industries with most Job Seekers</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_industry_most_jobseekers)){ foreach($top5_industry_most_jobseekers as $row){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=$row->industry_name?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$row->user_count?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Industries with most Employers</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">

              <li class="mb-6 d-flex justify-content-between align-items-center">
                <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                <div class="d-flex justify-content-between w-100 flex-wrap">
                  <h6 class="mb-0 ms-4">Emails</h6>
                  <div class="d-flex">
                    <p class="ms-4 text-success mb-0">0.3%</p>
                  </div>
                </div>
              </li>
              
            </ul>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Industries with most Jobs Posted</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_industry_most_jobs)){ foreach($top5_industry_most_jobs as $row){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=$row->industry_name?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$row->job_count?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
    <!--/ industry -->

    <!--/ top 5 functional area -->
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Functional Area with most Job Seekers</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_fa_most_jobseekers)){ foreach($top5_fa_most_jobseekers as $row){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=$row->fa_name?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$row->user_count?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Functional Area with most Jobs Posted</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_functional_area_most_jobs)){ foreach($top5_functional_area_most_jobs as $row){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=$row->fa_name?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$row->job_count?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
    <!--/ top 5 functional area -->

    <!--/ top 5 designation -->
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Designations with most Job Seekers</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_designation_most_jobseekers)){ foreach($top5_designation_most_jobseekers as $row){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=$row->designation_name?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$row->user_count?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Designations with most Jobs Posted</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_designation_most_jobs)){ foreach($top5_designation_most_jobs as $row){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=$row->designation_name?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$row->job_count?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
    <!--/ top 5 designation -->

    <!--/ top 5 nationality -->
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Nationalities with most Job Seekers</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_nationality_most_jobseeker)){ foreach($top5_nationality_most_jobseeker as $row){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=$row->nationality_name?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$row->user_count?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title mb-0"><h5 class="mb-1">Top 5 Nationalities with most Jobs Posted</h5></div>
          </div>
          <div class="card-body">
            <ul class="p-0 m-0">
              <?php if(!empty($top5_nationality_most_jobs)){ foreach($top5_nationality_most_jobs as $row){?>
                <li class="mb-6 d-flex justify-content-between align-items-center">
                  <div class="badge bg-label-success rounded p-1_5"><i class="icon-base ti tabler-mail icon-md"></i></div>
                  <div class="d-flex justify-content-between w-100 flex-wrap">
                    <h6 class="mb-0 ms-4"><?=$row->nationality_name?></h6>
                    <div class="d-flex">
                      <p class="ms-4 text-success mb-0"><?=$row->job_count?></p>
                    </div>
                  </div>
                </li>
              <?php } }?>
            </ul>
          </div>
        </div>
      </div>
    <!--/ top 5 nationality -->
  </div>
</div>
<!-- / Content -->
 @endsection

