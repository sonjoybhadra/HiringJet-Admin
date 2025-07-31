<?php
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
use App\Models\Employer;
use App\Models\Country;
use App\Models\City;
use App\Models\Nationality;
use App\Models\ContractType;
use App\Models\Keyskill;
use App\Models\CurrentWorkLevel;
use App\Models\Industry;
use App\Models\JobCategory;
use App\Models\Department;
use App\Models\FunctionalArea;
use App\Models\Designation;
?>
@extends('layouts.main')

@section('content')
<style>
    .form-label{
        font-weight: bold;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
   <div class="row g-6">
      <h4><?=$page_header?></h4>
      <h6 class="breadcrumb-wrapper">
            <span class="text-muted fw-light"><a href="<?=url('dashboard')?>">Dashboard</a> /</span>
            <span class="text-muted fw-light"><a href="<?=url($controllerRoute . '/list/')?>"><?=$module['title']?> List</a> /</span>
            <?=$page_header?>
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
            <?php
            if($row){
                $position_name                      = (($row->designation == 7037)?$row->position_name:'');
                $employer_id                        = $row->employer_id;
                $job_type                           = $row->job_type;
                $location_countries                 = (($row->location_countries != '')?json_decode($row->location_countries):[]);
                $location_cities                    = (($row->location_cities != '')?json_decode($row->location_cities):[]);
                $industry                           = $row->industry;
                $job_category                       = $row->job_category;
                $nationality                        = $row->nationality;
                $gender                             = $row->gender;
                $open_position_number               = $row->open_position_number;
                $contract_type                      = $row->contract_type;
                $designation                        = $row->designation;
                $functional_area                    = $row->functional_area;
                $min_exp_year                       = $row->min_exp_year;
                $max_exp_year                       = $row->max_exp_year;

                $job_description                    = $row->job_description;
                $requirement                        = $row->requirement;
                $skill_ids                          = (($row->skill_ids != '')?json_decode($row->skill_ids):[]);
                // $experience_level                   = $row->experience_level;
                $expected_close_date                = $row->expected_close_date;
                $currency                           = $row->currency;
                $min_salary                         = $row->min_salary;
                $max_salary                         = $row->max_salary;
                $is_salary_negotiable               = $row->is_salary_negotiable;
                $posting_open_date                  = $row->posting_open_date;
                $posting_close_date                 = $row->posting_close_date;

                $application_through                = $row->application_through;
                $apply_on_email                     = $row->apply_on_email;
                $apply_on_link                      = $row->apply_on_link;
                $walkin_address1                    = $row->walkin_address1;
                $walkin_address2                    = $row->walkin_address2;
                $walkin_country                     = $row->walkin_country;
                $walkin_state                       = $row->walkin_state;
                $walkin_city                        = $row->walkin_city;
                $walkin_pincode                     = $row->walkin_pincode;
                $walkin_latitude                    = $row->walkin_latitude;
                $walkin_longitude                   = $row->walkin_longitude;
                $walkin_details                     = $row->walkin_details;
            } else {
                $position_name                      = '';
                $employer_id                        = '';
                $job_type                           = '';
                $location_countries                 = [];
                $location_cities                    = [];
                $industry                           = '';
                $job_category                       = '';
                $nationality                        = '';
                $gender                             = '';
                $open_position_number               = '';
                $contract_type                      = '';
                $designation                        = '';
                $functional_area                    = '';
                $min_exp_year                       = '';
                $max_exp_year                       = '';

                $job_description                    = '';
                $requirement                        = '';
                $skill_ids                          = [];
                // $experience_level                   = '';
                $expected_close_date                = '';
                $currency                           = '';
                $min_salary                         = '';
                $max_salary                         = '';
                $is_salary_negotiable               = '';
                $posting_open_date                  = '';
                $posting_close_date                 = '';

                $application_through                = 'Hiring Jet';
                $apply_on_email                     = '';
                $apply_on_link                      = '';
                $walkin_address1                    = '';
                $walkin_address2                    = '';
                $walkin_country                     = '';
                $walkin_state                       = '';
                $walkin_city                        = '';
                $walkin_pincode                     = '';
                $walkin_latitude                    = '';
                $walkin_longitude                   = '';
                $walkin_details                     = '';
            }
            ?>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-6">
                        <!-- Account Details -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="text-primary">Step 1</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-6 mt-3">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="designation">Position Name: </label>                                        
                                        <span>
                                            <?php
                                            $getdesignation = Designation::select('name')->where('id', $designation)->first();
                                            echo (($getdesignation)?$getdesignation->name:'');
                                            ?>
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-3" id="role_name_column">
                                        <label class="form-label position_name" for="position_name" style="display: none;">Role Name: </label>
                                        <span><?=$position_name?></span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="employer_id">Employer: </label>
                                        <span>
                                            <?php
                                            $getEmployer = Employer::select('name')->where('id', $employer_id)->first();
                                            echo (($getEmployer)?$getEmployer->name:'');
                                            ?>
                                        </span>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="job_type">Job Type: </label>
                                        <span>
                                            <?=$job_type?>
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="open_position_number">Number of open positions: </label>
                                        <span>
                                            <?=$open_position_number?>
                                        </span>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="location_countries">Location Country:</label>
                                        <span>
                                            <?php
                                            $locationCountryNames = (($row->location_country_names != '')?json_decode($row->location_country_names):[]);
                                            if(!empty($locationCountryNames)) { echo implode(',', $locationCountryNames); }
                                            ?>
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="location_cities">Location City:</label>
                                        <span>
                                            <?php
                                            $locationCityNames = (($row->location_city_names != '')?json_decode($row->location_city_names):[]);
                                            if(!empty($locationCityNames)) { echo implode(',', $locationCityNames); }
                                            ?>
                                        </span>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="industry">Industry:</label>
                                        <?php
                                        $getIndustry = Industry::select('name')->where('id', $industry)->first();
                                        echo (($getIndustry)?$getIndustry->name:'');
                                        ?>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="job_category">Job Category:</label>
                                        <?php
                                        $getJobCategory = JobCategory::select('name')->where('id', $job_category)->first();
                                        echo (($getJobCategory)?$getJobCategory->name:'');
                                        ?>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="nationality">Nationality:</label>
                                        <?php
                                        $getNationality = Nationality::select('name')->where('id', $nationality)->first();
                                        echo (($getNationality)?$getNationality->name:'');
                                        ?>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="gender">Gender: </label>
                                        <span>
                                            <?=$gender?>
                                        </span>
                                    </div>
                                    
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="contract_type">Contract Type:</label>
                                        <?php
                                        $getContractType = ContractType::select('name')->where('id', $contract_type)->first();
                                        echo (($getContractType)?$getContractType->name:'');
                                        ?>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="functional_area">Functional Area:</label>
                                        <?php
                                        $getFunctionalArea = FunctionalArea::select('name')->where('id', $functional_area)->first();
                                        echo (($getFunctionalArea)?$getFunctionalArea->name:'');
                                        ?>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="min_exp_year">Min Experience:</label>
                                        <span>
                                            <?=$min_exp_year?> years
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="max_exp_year">Max Experience:</label>
                                        <span>
                                            <?=$max_exp_year?> years
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Personal Info -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="text-primary">Step 2</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-6 mt-3">
                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label" for="job_description">Job Description:</label>
                                        <span>
                                            <?=$job_description?>
                                        </span>
                                    </div>

                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label" for="requirement">Requirements:</label>
                                        <span>
                                            <?=$requirement?>
                                        </span>
                                    </div>                                    

                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label" for="skill_ids">Skills:</label>
                                        <span>
                                            <?php
                                            $skillNames = (($row->skill_names != '')?json_decode($row->skill_names):[]);
                                            if(!empty($skillNames)) { echo implode(',', $skillNames); }
                                            ?>
                                        </span>
                                    </div>
                                                                            
                                    <div class="col-sm-12 mb-3">
                                        <label for="is_salary_negotiable" class="form-label d-block">Mark salary is negotiable:</label>
                                        <div class="form-check form-switch mt-0 ">
                                            <span>
                                                <?=(($is_salary_negotiable)?'YES':'NO')?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php if(!$is_salary_negotiable){?>
                                        <div class="col-sm-4 mb-3 salary">
                                            <label class="form-label" for="currency">Currency:</label>
                                            <?=$currency?>
                                        </div>
                                        <div class="col-sm-4 mb-3 salary">
                                            <label class="form-label" for="min_salary">Minimum Salary:</label>
                                            <span>
                                                <?=$min_salary?>
                                            </span>
                                        </div>
                                        <div class="col-sm-4 mb-3 salary">
                                            <label class="form-label" for="max_salary">Maximum Salary:</label>
                                            <span>
                                                <?=$max_salary?>
                                            </span>
                                        </div>
                                    <?php }?>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="posting_open_date">Posting Open Date:</label>
                                        <span>
                                            <?=date_format(date_create($posting_open_date), "d-m-Y")?>
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="posting_close_date">Posting Close Date:</label>
                                        <span>
                                            <?=date_format(date_create($posting_close_date), "d-m-Y")?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Social Links -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="text-primary">Step 3</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-6 mt-3">
                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label" for="application_through">Accept Application Through:</label>
                                        <span>
                                            <?=$application_through?>
                                        </span>
                                    </div>
                                    <div class="col-sm-12 mb-3" id="apply_on_email_row" style="display:none;">
                                        <label class="form-label" for="apply_on_email">Apply To (Email):</label>
                                        <span>
                                            <?=$apply_on_email?>
                                        </span>
                                    </div>
                                    <div class="col-sm-12 mb-3" id="apply_on_link_row" style="display:none;">
                                        <label class="form-label" for="apply_on_link">Apply To (Link):</label>
                                        <span>
                                            <?=$apply_on_link?>
                                        </span>
                                    </div>

                                    <div class="col-sm-12">
                                        <h6>Office Address (for walk-ins)</h6>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label" for="walkin_details">Walkin Details:</label>
                                        <span>
                                            <?=$walkin_details?>
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_address1">Address Line 1:</label>
                                        <span>
                                            <?=$walkin_address1?>
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_address2">Address Line 2:</label>
                                        <span>
                                            <?=$walkin_address2?>
                                        </span>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_country">Country:</label>
                                        <span>
                                            <?=$walkin_country?>
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_state">State:</label>
                                        <span>
                                            <?=$walkin_state?>
                                        </span>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_city">City:</label>
                                        <span>
                                            <?=$walkin_city?>
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_pincode">Pincode:</label>
                                        <span>
                                            <?=$walkin_pincode?>
                                        </span>
                                    </div>

                                    <div class="col-12 d-flex justify-content-between">
                                        <a href="<?=url('post-job/delete/' . (($row)?Helper::encoded($row->id):''))?>" class="btn btn-danger me-1" onclick="return confirm('Do you want to delete this job ?');">Delete Job</a>
                                        <a href="<?=url('post-job/reject/' . (($row)?Helper::encoded($row->id):''))?>" class="btn btn-warning me-1" onclick="return confirm('Do you want to reject this job ?');">Reject Job</a>
                                        <a href="<?=url('post-job/approve/' . (($row)?Helper::encoded($row->id):''))?>" class="btn btn-success me-1" onclick="return confirm('Do you want to approve this job ?');">Approve Job</a>
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
@endsection