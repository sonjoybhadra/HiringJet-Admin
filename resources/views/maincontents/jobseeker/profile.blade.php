<?php
use App\Models\Country;
use App\Models\City;
use App\Models\UserSkill;
use App\Models\UserItSkill;
use App\Models\Keyskill;
use App\Models\ItSkill;
use App\Models\Industry;
use App\Models\UserEmployment;
use App\Models\UserEmploymentFunctionalArea;
use App\Models\UserEmploymentIndustry;
use App\Models\UserEmploymentParkBenefit;
use App\Models\UserEmploymentSkill;
use App\Models\UserExperience;
use App\Models\CurrentWorkLevel;
use App\Models\Designation;
use App\Models\Employer;
use App\Models\Availability;
use App\Models\MaritalStatus;
use App\Models\Nationality;
use App\Models\UserLanguage;
use App\Models\Language;
use App\Models\Religion;
use App\Models\UserEducation;
use App\Models\Qualification;
use App\Models\Course;
use App\Models\Specialization;
use App\Models\University;
use App\Models\ShortlistedJob;
use App\Models\PostJobUserApplied;
use App\Models\UserResume;
use App\Models\UserCertification;
use App\Models\UserOnlineProfile;
use App\Models\UserWorkSample;
use App\Models\UserProfileCompletedPercentage;
use App\Models\ProfileComplete;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
                      $getMaritalStatus = MaritalStatus::select('name')->where('id', $row->merital_status_id)->first();
                      $getNationality = Nationality::select('name')->where('id', $row->nationality_id)->first();
                      $getReligion = Religion::select('name')->where('id', $row->religion_id)->first();
                      $getNoticePeriod = Availability::select('name')->where('id', $row->availability_id)->first();

                      $user_skills = [];
                      $getUserSkills = UserSkill::select('keyskill_id')->where('user_id', '=', $id)->where('is_active', true)->get();
                      if($getUserSkills){
                        foreach($getUserSkills as $getUserSkill){
                          $getKeySkill = Keyskill::select('name')->where('id', '=', $getUserSkill->keyskill_id)->first();
                          if($getKeySkill){
                            $user_skills[] = $getKeySkill->name;
                          }
                        }
                      }

                      $user_it_skills = [];
                      $getUserITSkills = UserItSkill::select('itkill_id', 'exp_year', 'exp_month')->where('user_id', '=', $id)->get();
                      // Helper::pr($getUserITSkills);
                      if($getUserITSkills){
                        foreach($getUserITSkills as $getUserITSkill){
                          $getITSkill = ItSkill::select('name', 'version')->where('id', '=', $getUserITSkill->itkill_id)->first();
                          if($getITSkill){
                            $user_it_skills[] = [
                              'name'      => $getITSkill->name,
                              'version'   => $getITSkill->version,
                              'exp_year'  => $getITSkill->exp_year,
                              'exp_month' => $getITSkill->exp_month,
                            ];
                          }
                        }
                      }

                      $user_langs = [];
                      $getUserLangs = UserLanguage::select('language_id', 'proficiency_level')->where('user_id', '=', $id)->get();
                      if($getUserLangs){
                        foreach($getUserLangs as $getUserLang){
                          $getLang = Language::select('name')->where('id', '=', $getUserLang->language_id)->first();
                          if($getLang){
                            $user_langs[] = $getLang->name . ' ('.$getUserLang->proficiency_level.')';
                          }
                        }
                      }

                      $total_completed_percentage = 0;
                      $profile_completes_id = [];
                      $getProfilePercentages = DB::table('user_profile_completed_percentages')
                                                              ->join('profile_completes', 'user_profile_completed_percentages.profile_completes_id', '=', 'profile_completes.id')
                                                              ->select('profile_completes.percentage', 'profile_completes.name as percentage_name', 'user_profile_completed_percentages.profile_completes_id')
                                                              ->where('user_profile_completed_percentages.user_id', '=', $id)
                                                              ->orderBy('user_profile_completed_percentages.id', 'DESC')
                                                              ->get();
                      if($getProfilePercentages){
                        foreach($getProfilePercentages as $getProfilePercentage){
                          $total_completed_percentage += $getProfilePercentage->percentage;
                          $profile_completes_id[] = $getProfilePercentage->profile_completes_id;
                        }
                      }
                      ?>
                      <div class="card mb-6">
                        <!-- <div class="user-profile-header-banner">
                          <img src="{{ config('constants.admin_assets_url') }}assets/img/pages/profile-banner.png" alt="Banner image" class="rounded-top" />
                        </div> -->
                        <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-3">
                          <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                            <?php if($row->profile_image == null){?>
                              <img src="{{ config('constants.admin_assets_url') }}assets/img/avatars/no-image.jpg" alt="<?=$row->first_name.' '.$row->last_name?>" class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" style="width: 100px;height: 100px;" />
                            <?php } else {?>
                              <img src="<?=url('/').'/'.$row->profile_image?>" alt="<?=$row->first_name.' '.$row->last_name?>" class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" style="width: 100px;" />
                            <?php }?>
                          </div>
                          <div class="flex-grow-1 mt-3 mt-lg-5">
                            <div
                              class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                              <div class="user-profile-info">
                                <h4 class="mb-2 mt-lg-6"><?=$row->first_name.' '.$row->last_name?>
                                  <div class="progress" style="height: 20px">
                                    <div
                                      class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                      role="progressbar"
                                      style="width: <?=$total_completed_percentage?>%"
                                      aria-valuenow="<?=$total_completed_percentage?>"
                                      aria-valuemin="0"
                                      aria-valuemax="100">
                                      Profile percentage :<?=$total_completed_percentage?>%
                                    </div>
                                  </div>
                                </h4>
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
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-1" aria-controls="navs-pills-justified-profile" aria-selected="true">Professional</button>
                          </li>
                          <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-2" aria-controls="navs-pills-justified-general" aria-selected="false">Personal</button>
                          </li>
                          <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-3" aria-controls="navs-pills-justified-password" aria-selected="false">Educational</button>
                          </li>
                          <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-4" aria-controls="navs-pills-justified-password" aria-selected="false">Employment</button>
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
                          <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-10" aria-controls="navs-pills-justified-password" aria-selected="false">Profile (%)</button>
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
                                    <?php if(!empty($user_skills)){ for($k=0;$k<count($user_skills);$k++){?>
                                    <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5"><?=$user_skills[$k]?></button>
                                    <?php } }?>
                                  </div>
                                </div>
                                <div class="card mb-4">
                                  <div class="card-body">
                                    <h5>Professional Details</h5>
                                    <?php
                                    $user_employment = UserEmployment::select('total_experience_years', 'total_experience_months', 'work_level', 'currency_id', 'current_salary', 'id')->where('user_id', '=', $id)->where('is_current_job', true)->where('is_active', true)->first();
                                    if($user_employment){
                                      $getCurrency = Country::select('currency_code')->where('id', $user_employment->currency_id)->first();
                                      $getWorkLevel = CurrentWorkLevel::select('name')->where('id', $user_employment->work_level)->first();
                                      $user_employment_id = $user_employment->id;
                                      $getEmploymentIndustry = DB::table('user_employment_industries')
                                                                ->join('industries', 'user_employment_industries.industry', '=', 'industries.id')
                                                                ->select('industries.name as industry_name')
                                                                ->where('user_employment_industries.user_employment_id', '=', $user_employment_id)
                                                                ->where('user_employment_industries.user_id', '=', $id)
                                                                ->orderBy('user_employment_industries.id', 'DESC')
                                                                ->first();

                                      $getEmploymentFunctionalArea = DB::table('user_employment_functional_areas')
                                                                ->join('functional_areas', 'user_employment_functional_areas.functional_area', '=', 'functional_areas.id')
                                                                ->select('functional_areas.name as fa_name')
                                                                ->where('user_employment_functional_areas.user_employment_id', '=', $user_employment_id)
                                                                ->where('user_employment_functional_areas.user_id', '=', $id)
                                                                ->orderBy('user_employment_functional_areas.id', 'DESC')
                                                                ->first();

                                      $getEmploymentPerks = DB::table('user_employment_park_benefits')
                                                                ->join('perk_benefits', 'user_employment_park_benefits.perk_benefit', '=', 'perk_benefits.id')
                                                                ->select('perk_benefits.name as perk_name')
                                                                ->where('user_employment_park_benefits.user_employment_id', '=', $user_employment_id)
                                                                ->where('user_employment_park_benefits.user_id', '=', $id)
                                                                ->orderBy('user_employment_park_benefits.id', 'DESC')
                                                                ->first();
                                    ?>
                                      <div class="row">
                                        <div class="col-md-4 mb-3">
                                          <h6>Total Work Experience</h6>
                                          <span><?=$user_employment->total_experience_years?> years, <?=(($user_employment->total_experience_months != '')?$user_employment->total_experience_months . ' months':'')?></span>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                          <h6>Industry</h6>
                                          <span><?=(($getEmploymentIndustry)?$getEmploymentIndustry->industry_name:'')?></span>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                          <h6>Functional Area</h6>
                                          <span><?=(($getEmploymentFunctionalArea)?$getEmploymentFunctionalArea->fa_name:'')?></span>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                          <h6>Current Work Level</h6>
                                          <span><?=(($getWorkLevel)?$getWorkLevel->name:'')?></span>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                          <h6>Monthly Salary</h6>
                                          <span><?=(($getCurrency)?$getCurrency->currency_code:'')?> <?=$user_employment->current_salary?></span>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                          <h6>Additional Perks & Benefits</h6>
                                          <span><?=(($getEmploymentPerks)?$getEmploymentPerks->perk_name:'')?></span>
                                        </div>
                                      </div>
                                    <?php } ?>
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
                                      <?php if(!empty($user_it_skills)){ for($k=0;$k<count($user_it_skills);$k++){?>
                                        <div class="col-md-6">
                                          <div class="card">
                                            <h5 class="card-header"><?=$user_it_skills[$k]['name']?></h5>
                                            <div class="card-body">
                                              <small class="text-muted">Version: <?=$user_it_skills[$k]['version']?></small><br>
                                              <small class="text-muted">Experience: <?=$user_it_skills[$k]['exp_year']?> years <?=$user_it_skills[$k]['exp_month']?> months</small><br>
                                            </div>
                                          </div>
                                        </div>
                                      <?php } }?>
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
                                        <span><?=(($row->date_of_birth != '')?date_format(date_create($row->date_of_birth), "d/m/Y"):'')?></span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>Gender</h6>
                                        <span><?=$row->gender?></span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>Marital Status</h6>
                                        <span><?=(($getMaritalStatus)?$getMaritalStatus->name:'')?></span>
                                      </div>
                                      <hr>

                                      <div class="col-md-4 mb-3">
                                        <h6>Nationality</h6>
                                        <span><?=(($getNationality)?$getNationality->name:'')?></span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>Language Known</h6>
                                        <span><?=implode(', ', $user_langs)?></span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>Category</h6>
                                        <span><?=$row->cast_category?></span>
                                      </div>
                                      <hr>

                                      <div class="col-md-4 mb-3">
                                        <h6>Religion</h6>
                                        <span><?=(($getReligion)?$getReligion->name:'')?></span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>Country</h6>
                                        <span><?=(($getCountry)?$getCountry->name:'')?></span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>Current Location</h6>
                                        <span><?=(($getCity)?$getCity->name:'')?></span>
                                      </div>
                                      <hr>

                                      <div class="col-md-4 mb-3">
                                        <h6>Career Break</h6>
                                        <span><?=(($row->career_break)?'Yes':'No')?></span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>Differently Abled</h6>
                                        <span><?=(($row->differently_abled)?'Yes':'No')?></span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>USA Work Permit</h6>
                                        <span><?=(($row->usa_working_permit)?'Yes':'No')?></span>
                                      </div>
                                      <hr>

                                      <div class="col-md-4 mb-3">
                                        <h6>Other Countries Work Permit</h6>
                                        <span>
                                          <?php
                                          $couns = [];
                                          $other_working_permit_country = (($row->other_working_permit_country != '')?explode(",", json_decode($row->other_working_permit_country)[0]):[]);
                                          if(!empty($other_working_permit_country)){
                                            for($p=0;$p<count($other_working_permit_country);$p++){
                                              $getCountry = Country::select('country_short_code')->where('id', $other_working_permit_country[$p])->first();
                                              $couns[] = (($getCountry)?$getCountry->country_short_code:'');
                                            }
                                            echo implode(', ', $couns);
                                          }
                                          ?>
                                        </span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>Driving License</h6>
                                        <span><?=(($row->has_driving_license)?'Yes':'No')?></span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>Alternative Email Address</h6>
                                        <span><?=$row->alt_email?></span>
                                      </div>
                                      <hr>

                                      <div class="col-md-4 mb-3">
                                        <h6>Alternative Phone</h6>
                                        <span><?=$row->alt_phone?></span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>Permanent Address</h6>
                                        <span><?=$row->address?></span>
                                      </div>
                                      <div class="col-md-4 mb-3">
                                        <h6>Pincode</h6>
                                        <span><?=$row->pincode?></span>
                                      </div>
                                      <hr>

                                      <div class="col-md-4 mb-3">
                                        <h6>Diverse Background</h6>
                                        <span><?=(($row->diverse_background)?$row->diverse_background:'Not specified')?></span>
                                      </div>
                                    </div>
                                  </div>
                              </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-justified-3" role="tabpanel">
                              <h5>Educational Info</h5>
                              <div class="card mb-4">
                                  <div class="card-body">
                                    <?php
                                    $user_educations = UserEducation::select('course_id', 'specialization_id', 'location_id', 'university_id', 'course_type', 'grade', 'course_start_year', 'course_end_year')->where('user_id', '=', $id)->get();
                                    if($user_educations){ foreach($user_educations as $user_education){
                                      $getCourse = Course::select('name')->where('id', $user_education->course_id)->first();
                                      $getSpecialization = Specialization::select('name')->where('id', $user_education->specialization_id)->first();
                                      $getUniversity = University::select('name')->where('id', $user_education->university_id)->first();
                                      $getLocation = City::select('name')->where('id', $user_education->location_id)->first();
                                    ?>
                                      <div class="list-group">
                                        <div class="list-group-item list-group-item-action d-flex align-items-center cursor-pointer">
                                          <span class="rounded-circle me-3" width="40"><i class="fa fa-book fa-2x text-primary" style="border: 1px solid #092b61;padding: 15px;border-radius: 50%;"></i></span>
                                          <div class="w-100">
                                            <div class="d-flex justify-content-between">
                                              <div class="user-info">
                                                <h5 class="mb-1 fw-normal"><?=(($getCourse)?$getCourse->name:'')?></h5>
                                                <span class="text-primary"><?=(($getUniversity)?$getUniversity->name:'')?> <?php if($user_education->course_start_year != ''){?>(<?=$user_education->course_start_year?> - <?=$user_education->course_end_year?>)<?php }?></span><br>
                                                <small class="text-muted">Specialization: <?=(($getSpecialization)?$getSpecialization->name:'')?></small><br>
                                                <small class="text-muted">Course Type: <?=$user_education->course_type?></small><br>
                                                <small class="text-muted">Grade: <?=$user_education->grade?></small><br>
                                                <small class="text-muted">Location: <?=(($getLocation)?$getLocation->name:'')?></small>
                                              </div>
                                              <!-- <div class="add-btn">
                                                <button class="btn btn-primary btn-sm">Add</button>
                                              </div> -->
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    <?php } }?>
                                  </div>
                              </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-justified-4" role="tabpanel">
                              <h5>Employment Info</h5>
                              <div class="card mb-4">
                                  <div class="card-body">
                                    <div class="demo-inline-spacing mt-4">
                                      <div class="list-group">
                                        <?php
                                        $user_employments = UserEmployment::where('user_id', '=', $id)->where('is_active', true)->orderBy('id', 'ASC')->get();
                                        if($user_employments){ foreach($user_employments as $user_employment_row){
                                          $getDesignation = Designation::select('name')->where('id', '=', $user_employment_row->last_designation)->first();
                                          $getEmployer = Employer::select('name')->where('id', '=', $user_employment_row->employer_id)->first();
                                          $getCurrency = Country::select('currency_code')->where('id', $user_employment_row->currency_id)->first();
                                          $getNoticePeriod = Availability::select('name')->where('id', $user_employment_row->notice_period)->first();

                                          $getEmploymentSkills = DB::table('user_employment_skills')
                                                                ->join('keyskills', 'user_employment_skills.keyskill_id', '=', 'keyskills.id')
                                                                ->select('keyskills.name as key_skill_name')
                                                                ->where('user_employment_skills.user_employment_id', '=', $user_employment_row->id)
                                                                ->where('user_employment_skills.user_id', '=', $id)
                                                                ->orderBy('user_employment_skills.id', 'DESC')
                                                                ->get();
                                          $skills = [];
                                          if($getEmploymentSkills){
                                            foreach($getEmploymentSkills as $getEmploymentSkill){
                                              $skills[] = $getEmploymentSkill->key_skill_name;
                                            }
                                          }
                                        ?>
                                          <div class="list-group-item list-group-item-action d-flex align-items-center cursor-pointer">
                                            <span class="rounded-circle me-3" width="40"><i class="fa fa-briefcase fa-2x text-primary" style="border: 1px solid #092b61;padding: 15px;border-radius: 50%;"></i></span>
                                            <div class="w-100">
                                              <div class="d-flex justify-content-between">
                                                <div class="user-info">
                                                  <h5 class="mb-1 fw-normal"><?=(($getDesignation)?$getDesignation->name:'')?></h5>

                                                  <?php
                                                  $fromMonthName = Carbon::create()->month($user_employment_row->working_since_from_month)->format('F');
                                                  $toMonthName = Carbon::create()->month($user_employment_row->working_since_to_month)->format('F');
                                                  ?>
                                                  <span class="text-primary"><?=(($getEmployer)?$getEmployer->name:'')?> (<?=$fromMonthName?> <?=$user_employment_row->working_since_from_year?> - <?=((!$user_employment_row->is_current_job)?$toMonthName . ' ' . $user_employment_row->working_since_to_year:'Present')?>)</span><br>

                                                  <small class="text-muted">Experiences: <?=$user_employment_row->total_experience_years?> years, <?=(($user_employment_row->total_experience_months != '')?$user_employment_row->total_experience_months . ' months':'')?></small><br>
                                                  <small class="text-muted">Type: <?=$user_employment_row->employment_type?></small><br>
                                                  <small class="text-muted">Salry: <?=(($getCurrency)?$getCurrency->currency_code:'')?> <?=$user_employment_row->current_salary?></small><br>
                                                  <small class="text-muted">Notice Period: <?=(($getNoticePeriod)?$getNoticePeriod->name:'')?></small><br>
                                                  <small class="text-muted">Skills: <?=implode(', ', $skills)?></small>
                                                </div>
                                                <!-- <div class="add-btn">
                                                  <button class="btn btn-primary btn-sm">Add</button>
                                                </div> -->
                                              </div>
                                            </div>
                                          </div>
                                        <?php } }?>
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
                                            <?php
                                            $user_certifications = UserCertification::where('user_id', '=', $id)->get();
                                            if($user_certifications){ foreach($user_certifications as $user_certification){
                                            ?>
                                              <div class="card">
                                                <div class="row g-0">
                                                  <div class="col-md-1">
                                                    <?php if($user_certification->certification_image == ''){?>
                                                      <img class="card-img card-img-left" src="https://static.vecteezy.com/system/resources/previews/006/414/199/non_2x/globe-icon-internet-web-symbol-isolated-on-white-background-free-vector.jpg" alt="<?=$user_certification->certification_name?>" style="width:75px; height:75px;">
                                                    <?php } else {?>
                                                      <img class="card-img card-img-left" src="<?=url('/').'/'.$user_certification->certification_image?>" alt="<?=$user_certification->certification_name?>" style="width:100px; height:100px;">
                                                    <?php }?>
                                                  </div>
                                                  <div class="col-md-11">
                                                    <div class="card-body">
                                                      <h5 class="card-title"><?=$user_certification->certification_name?></h5>
                                                      <small class="text-muted"><?=$user_certification->certification_provider?></small><br>

                                                      <?php
                                                      $from_month   = Carbon::create()->month($user_certification->from_month)->format('F');
                                                      $from_year    = $user_certification->from_year;
                                                      $to_month     = Carbon::create()->month($user_certification->to_month)->format('F');
                                                      $to_year      = $user_certification->to_year;
                                                      ?>
                                                      <small class="text-muted"><?=$from_month?> <?=$from_year?> <?=(($user_certification->to_month > 0 && $user_certification->to_year > 0)?' - '.$to_month.' '.$to_year:'')?></small>
                                                      <p class="card-text">
                                                        <small class="text-body-secondary">
                                                          <?php if($user_certification->certification_url != ''){?>
                                                            <p><a href="<?=$user_certification->certification_url?>" target="_blank" class="text-primary"><i class="fa fa-paper-clip"></i> View Certificate Online</a></p>
                                                          <?php }?>
                                                          <span class="<?=(($user_certification->has_expire)?'text-danger':'text-success')?>"><?=(($user_certification->has_expire)?'Expired':'Not Expired')?></span>
                                                        </small>
                                                      </p>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            <?php } }?>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="card mb-4">
                                      <div class="card-body">
                                        <h5>Online Profile</h5>
                                        <div class="row">
                                          <div class="col-md-12">
                                            <?php
                                            $user_online_profiles = UserOnlineProfile::where('user_id', '=', $id)->get();
                                            if($user_online_profiles){ foreach($user_online_profiles as $user_online_profile){
                                            ?>
                                              <div class="card">
                                                <div class="row g-0">
                                                  <div class="col-md-1">
                                                    <img class="card-img card-img-left" src="https://static.vecteezy.com/system/resources/previews/006/414/199/non_2x/globe-icon-internet-web-symbol-isolated-on-white-background-free-vector.jpg" alt="Card image" style="width:75px; height:75px;">
                                                  </div>
                                                  <div class="col-md-11">
                                                    <div class="card-body">
                                                      <h5 class="card-title"><?=ucwords(str_replace("_", " ", $user_online_profile->profile_key))?></h5>
                                                      <p class="card-text">
                                                        <small class="text-body-secondary">
                                                          <a href="<?=$user_online_profile->value?>" target="_blank" class="text-primary"><i class="fa fa-paper-clip"></i> <?=$user_online_profile->value?></a>
                                                        </small>
                                                      </p>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            <?php } }?>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="card mb-4">
                                      <div class="card-body">
                                        <h5>Work Sample / Project</h5>
                                        <div class="row">
                                          <div class="col-md-12">
                                            <?php
                                            $user_work_projects = UserWorkSample::where('user_id', '=', $id)->get();
                                            if($user_work_projects){ foreach($user_work_projects as $user_work_project){
                                            ?>
                                              <div class="card">
                                                <div class="row g-0">
                                                  <div class="col-md-1">
                                                    <?php if($user_work_project->sample_image == ''){?>
                                                      <img class="card-img card-img-left" src="https://static.vecteezy.com/system/resources/previews/006/414/199/non_2x/globe-icon-internet-web-symbol-isolated-on-white-background-free-vector.jpg" alt="<?=$user_work_project->sample_title?>" style="width:75px; height:75px;">
                                                    <?php } else {?>
                                                      <img class="card-img card-img-left" src="<?=url('/').'/'.$user_work_project->sample_image?>" alt="<?=$user_work_project->sample_title?>" style="width:100px; height:100px;">
                                                    <?php }?>
                                                  </div>
                                                  <div class="col-md-11">
                                                    <div class="card-body">
                                                      <h5 class="card-title"><?=$user_work_project->sample_title?></h5>
                                                      <small class="text-muted"><?=$user_work_project->sample_description?></small><br>

                                                      <?php
                                                      $from_month   = Carbon::create()->month($user_work_project->from_month)->format('F');
                                                      $from_year    = $user_work_project->from_year;
                                                      $to_month     = Carbon::create()->month($user_work_project->to_month)->format('F');
                                                      $to_year      = $user_work_project->to_year;
                                                      ?>
                                                      <small class="text-muted"><?=$from_month?> <?=$from_year?> <?=(($user_work_project->to_month > 0 && $user_work_project->to_year > 0)?' - '.$to_month.' '.$to_year:'')?></small>
                                                      <p class="card-text">
                                                        <small class="text-body-secondary">
                                                          <?php if($user_work_project->sample_url != ''){?>
                                                            <p><a href="<?=$user_work_project->sample_url?>" target="_blank" class="text-primary"><i class="fa fa-paper-clip"></i> View Work Sample Online</a></p>
                                                          <?php }?>
                                                          <span class="<?=(($user_work_project->currently_working)?'text-primary':'text-success')?>"><?=(($user_work_project->currently_working)?'Currently working':'Completed')?></span>
                                                        </small>
                                                      </p>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            <?php } }?>
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
                                    <div class="row">
                                      <h6 class="mt-3">Job Type</h6>
                                      <div class="col-md-6">
                                        <h6 class="mt-3"><input type="checkbox" <?=(($row->job_type_temp)?'checked':'')?>> Temp</h6>
                                        <ul style="list-style: none;">
                                          <li><input type="checkbox" <?=(($row->temp_remote)?'checked':'')?>> Remote</li>
                                          <li><input type="checkbox" <?=(($row->temp_onsite)?'checked':'')?>> On-Site</li>
                                          <li><input type="checkbox" <?=(($row->temp_hybrid)?'checked':'')?>> Hybrid Roles</li>
                                        </ul>
                                      </div>
                                      <div class="col-md-6">
                                        <h6 class="mt-3">
                                          <input type="checkbox" <?=(($row->job_type_permanent)?'checked':'')?>> Permanent
                                        </h6>
                                        <ul style="list-style: none;">
                                          <li><input type="checkbox" <?=(($row->permanent_remote)?'checked':'')?>> Remote</li>
                                          <li><input type="checkbox" <?=(($row->permanent_onsite)?'checked':'')?>> On-Site</li>
                                          <li><input type="checkbox" <?=(($row->permanent_hybrid)?'checked':'')?>> Hybrid Roles</li>
                                        </ul>
                                      </div>

                                      <div class="col-md-6">
                                        <h6 class="mt-3">Prefered Designation</h6>
                                        <?php
                                        $preferred_designations = (($row->preferred_designation != '')?json_decode($row->preferred_designation):[]);
                                        $preferred_locations = (($row->preferred_location != '')?json_decode($row->preferred_location):[]);
                                        $preferred_industries = (($row->preferred_industry != '')?json_decode($row->preferred_industry):[]);
                                        ?>
                                        <?php if(!empty($preferred_designations)){ foreach($preferred_designations as $preferred_designation){?>
                                          <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5"><?=$preferred_designation->name?></button>
                                        <?php } }?>
                                      </div>

                                      <div class="col-md-6">
                                        <h6 class="mt-3">Prefered Location</h6>
                                        <?php if(!empty($preferred_locations)){ foreach($preferred_locations as $preferred_location){?>
                                          <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5"><?=$preferred_location->name?></button>
                                        <?php } }?>
                                      </div>

                                      <div class="col-md-6">
                                        <h6 class="mt-3">Prefered Industry</h6>
                                        <?php if(!empty($preferred_industries)){ foreach($preferred_industries as $preferred_industry){?>
                                          <button type="button" class="btn rounded-pill btn-outline-secondary waves-effect mr-5"><?=$preferred_industry->name?></button>
                                        <?php } }?>
                                      </div>

                                      <div class="col-md-6">
                                        <h6 class="mt-3">Availability to join</h6>
                                        <span><?=(($getNoticePeriod)?$getNoticePeriod->name:'')?></span>
                                      </div>
                                    </div>
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
                                            <?=$row->first_name.' '.$row->last_name?><br>
                                            Curriculum Vitae
                                          </h5>
                                          <?php
                                          $getResume = UserResume::select('cv')->where('user_id', '=', $id)->where('is_default', '=', 1)->orderBy('id', 'DESC')->first();
                                          if($getResume){ if($getResume->cv != null){
                                          ?>
                                            <div class="card-body">
                                              <a href="<?=url('/').'/'.$getResume->cv?>" target="_blank" class="btn btn-outline-info mt-3 mb-3">View CV</a>
                                            </div>
                                          <?php } }?>
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
                                          <?php
                                          $jobApplications = DB::table('post_job_user_applieds')
                                                                  ->join('post_jobs', 'post_job_user_applieds.job_id', '=', 'post_jobs.id')
                                                                  ->join('employers', 'post_jobs.employer_id', '=', 'employers.id')
                                                                  ->select('post_jobs.position_name', 'employers.name as employer_name', 'post_job_user_applieds.created_at')
                                                                  ->where('post_job_user_applieds.user_id', '=', $id)
                                                                  ->where('post_job_user_applieds.status', '=', 1)
                                                                  ->orderBy('post_job_user_applieds.id', 'DESC')
                                                                  ->get();
                                          if(count($jobApplications)>0){ $sl=1; foreach($jobApplications as $jobApplication){
                                          ?>
                                            <tr>
                                              <th scope="row"><?=$sl++?></th>
                                              <td><?=$jobApplication->employer_name?></td>
                                              <td><?=$jobApplication->position_name?></td>
                                              <td><?=$jobApplication->created_at?></td>
                                            </tr>
                                          <?php } } else {?>
                                            <tr>
                                              <td colspan="4" style="text-align: center; color: red;">No applied jobs found</td>
                                            </tr>
                                          <?php }?>
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
                                          <?php
                                          $jobShortLists = DB::table('shortlisted_jobs')
                                                                  ->join('post_jobs', 'shortlisted_jobs.job_id', '=', 'post_jobs.id')
                                                                  ->join('employers', 'post_jobs.employer_id', '=', 'employers.id')
                                                                  ->select('post_jobs.position_name', 'employers.name as employer_name', 'shortlisted_jobs.created_at')
                                                                  ->where('shortlisted_jobs.user_id', '=', $id)
                                                                  ->where('shortlisted_jobs.status', '=', 1)
                                                                  ->orderBy('shortlisted_jobs.id', 'DESC')
                                                                  ->get();
                                          if(count($jobShortLists)>0){ $sl=1; foreach($jobShortLists as $jobShortList){
                                          ?>
                                            <tr>
                                              <th scope="row"><?=$sl++?></th>
                                              <td><?=$jobShortList->employer_name?></td>
                                              <td><?=$jobShortList->position_name?></td>
                                              <td><?=$jobShortList->created_at?></td>
                                            </tr>
                                          <?php } } else {?>
                                            <tr>
                                              <td colspan="4" style="text-align: center; color: red;">No shortlisted jobs found</td>
                                            </tr>
                                          <?php }?>
                                        </tbody>
                                      </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-justified-10" role="tabpanel">
                              <h5>Profile Percentage</h5>
                              <div class="card mb-4">
                                <div class="card-body">
                                  <table id="simpletable" class="table table-striped table-bordered nowrap">
                                    <thead>
                                      <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Parameter Name</th>
                                      <th scope="col">Percentage</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php $sl=1;$total=0; if(count($getProfilePercentages)>0){ foreach($getProfilePercentages as $getProfilePercentage){ ?>
                                        <?php $total += $getProfilePercentage->percentage; ?>
                                        <tr>
                                          <th scope="row"><?=$sl++?></th>
                                          <td><?=$getProfilePercentage->percentage_name?></td>
                                          <td><?=$getProfilePercentage->percentage?>%</td>
                                        </tr>
                                      <?php } } else {?>
                                        <tr>
                                          <td colspan="3" style="text-align: center; color: red;">No profile percentage parameters found</td>
                                        </tr>
                                      <?php }?>
                                    </tbody>
                                    <tfoot>
                                      <tr>
                                        <th scope="col"></th>
                                        <th scope="col" style="float: right;">Total <i class="fa fa-arrow-right"></i></th>
                                        <th scope="col"><?=$total?>%</th>
                                      </tr>
                                    </tfoot>
                                  </table>

                                  <h5>Pending Points</h5>
                                  <table id="simpletable" class="table table-striped table-bordered nowrap">
                                    <thead>
                                      <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Parameter Name</th>
                                        <th scope="col">Percentage</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php
                                      $profile_completes = ProfileComplete::select('id', 'name', 'percentage')->where('status', '=', 1)->get();
                                      if(count($profile_completes)>0){ $sl=1;$total=0; foreach($profile_completes as $profile_complete){
                                      ?>
                                        <?php $total += $profile_complete->percentage; ?>
                                        <?php if(!in_array($profile_complete->id, $profile_completes_id)){?>
                                          <tr>
                                            <th scope="row"><?=$sl++?></th>
                                            <td><?=$profile_complete->name?></td>
                                            <td><?=$profile_complete->percentage?>%</td>
                                          </tr>
                                        <?php }?>
                                      <?php } } else {?>
                                        <tr>
                                          <td colspan="3" style="text-align: center; color: red;">No profile percentage parameters found</td>
                                        </tr>
                                      <?php }?>
                                    </tbody>
                                    <!-- <tfoot>
                                      <tr>
                                        <th scope="col"></th>
                                        <th scope="col" style="float: right;">Total <i class="fa fa-arrow-right"></i></th>
                                        <th scope="col"><?=$total?>%</th>
                                      </tr>
                                    </tfoot> -->
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