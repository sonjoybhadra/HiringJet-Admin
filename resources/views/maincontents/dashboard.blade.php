
<?php
use App\Helpers\Helper;
?>
@extends('layouts.main')
@section('title', 'Dashboard')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row g-6">
    <h2 class="mt-3 mb-3">Welcome to <?=Helper::getSettingValue('site_name')?> admin panel</h2>
    <!-- Average Daily Sales -->
      <div class="col-xl-2 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Total Job Seekers</h5>
            <h4 class="mb-3">0</h4>
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
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Active Job Seekers in last 30 Days</h5>
            <h4 class="mb-3">0</h4>
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
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
    <!--/ Average Daily Sales -->

    <!--/ Average Daily Sales -->
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Countries with most Job Seekers</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Countries with most Employers</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Countries with most Jobs Posted</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
    <!--/ Average Daily Sales -->

    <!--/ Average Daily Sales -->
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Industries with most Job Seekers</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Industries with most Employers</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Industries with most Jobs Posted</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
    <!--/ Average Daily Sales -->

    <!--/ Average Daily Sales -->
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Roles with most Job Seekers</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Roles with most Jobs Posted</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
    <!--/ Average Daily Sales -->

    <!--/ Average Daily Sales -->
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Designations with most Job Seekers</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Designations with most Jobs Posted</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
    <!--/ Average Daily Sales -->

    <!--/ Average Daily Sales -->
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Nationalities with most Job Seekers</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
      <div class="col-xl-6 col-sm-6 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h5 class="mb-3 card-title">Top 5 Nationalities with most Employers</h5>
            <h4 class="mb-3">0</h4>
          </div>
        </div>
      </div>
    <!--/ Average Daily Sales -->
  </div>
</div>
<!-- / Content -->
 @endsection

