<?php
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
?>
@extends('layouts.main')

<script>
    let autocomplete;
    let address1Field;
    let address2Field;
    let postalField;
    
    function initAutocomplete() {
        address1Field = document.querySelector("#walkin_address1");
        address2Field = document.querySelector("#walkin_address2");
        postalField = document.querySelector("#walkin_pincode");
        autocomplete = new google.maps.places.Autocomplete(address1Field, {
        componentRestrictions: { country: [] },
        fields: ["address_components", "geometry", "formatted_address"],
        types: ["address"],
        });
        address1Field.focus();
        autocomplete.addListener("place_changed", fillInAddress);
    }
    
    function fillInAddress() {
        const place = autocomplete.getPlace();
        let address1 = "";
        let postcode = "";
        for (const component of place.address_components) {
        const componentType = component.types[0];
        switch (componentType) {
            case "postal_code": {
            postcode = `${component.long_name}${postcode}`;
            break;
            }
            case "postal_code_suffix": {
            postcode = `${postcode}-${component.long_name}`;
            break;
            }
            case "street_number": {
            document.querySelector("#walkin_address2").value = component.long_name;
            break;
            }
            case "route": {
            document.querySelector("#walkin_city").value = component.long_name;
            break;
            }
            case "locality": {
            document.querySelector("#walkin_city").value = component.long_name;
            break;
            }
            case "administrative_area_level_1": {
            document.querySelector("#walkin_state").value = component.long_name;
            break;
            }
            case "country":
            document.querySelector("#walkin_country").value = component.long_name;
            break;
            }
        }
        address1Field.value = place.formatted_address;
        postalField.value = postcode;
        document.querySelector("#walkin_latitude").value = place.geometry.location.lat();
        document.querySelector("#walkin_longitude").value = place.geometry.location.lng();
        address2Field.focus();
    }
    window.initAutocomplete = initAutocomplete;
</script>

@section('content')
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
                    <div id="table-overlay-loader" class="text-loader" style="display: none;">
                        Fetching cities. Please wait <span id="dot-animation">.</span>
                    </div>
                    <!-- Validation Wizard -->
                    <div class="col-12 mb-6">
                    <!-- <small class="text-light fw-medium">Validation</small> -->
                    <div id="wizard-validation" class="bs-stepper mt-2">
                        <div class="bs-stepper-header">
                            <div class="step" data-target="#account-details-validation">
                                <button type="button" class="step-trigger">
                                <span class="bs-stepper-circle">1</span>
                                <span class="bs-stepper-label mt-1">
                                    <span class="bs-stepper-title">Step 1</span>
                                    <span class="bs-stepper-subtitle">Setup Step 1</span>
                                </span>
                                </button>
                            </div>
                            <div class="line">
                                <i class="ti ti-chevron-right"></i>
                            </div>
                            <div class="step" data-target="#personal-info-validation">
                                <button type="button" class="step-trigger">
                                <span class="bs-stepper-circle">2</span>
                                <span class="bs-stepper-label">
                                    <span class="bs-stepper-title">Step 2</span>
                                    <span class="bs-stepper-subtitle">Add Step 2</span>
                                </span>
                                </button>
                            </div>
                            <div class="line">
                                <i class="ti ti-chevron-right"></i>
                            </div>
                            <div class="step" data-target="#social-links-validation">
                                <button type="button" class="step-trigger">
                                <span class="bs-stepper-circle">3</span>
                                <span class="bs-stepper-label">
                                    <span class="bs-stepper-title">Step 3</span>
                                    <span class="bs-stepper-subtitle">Add Step 3</span>
                                </span>
                                </button>
                            </div>
                        </div>
                        <div class="bs-stepper-content">
                        <form id="wizard-validation-form" method="POST" onSubmit="return false">
                            @csrf
                            <!-- Account Details -->
                            <div id="account-details-validation" class="content">
                                <div class="content-header mb-4">
                                    <h6 class="mb-0">Step 1</h6>
                                    <small>Enter Your Step 1.</small>
                                    <h6 class="text-danger">Star (*) marks fields are mandatory</h6>
                                </div>
                                <div class="row g-6 mt-3" id="step1">
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="designation">Position Name <span class="text-danger">*</span></label>
                                        <select class="select2" id="designation" name="designation">
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <?php if($designations){ foreach($designations as $select_row){?>
                                                <option value="<?=$select_row->id?>" <?=(($designation == $select_row->id)?'selected':'')?>><?=$select_row->name?></option>
                                            <?php } }?>
                                        </select>
                                        
                                    </div>
                                    <div class="col-sm-6 mb-3" id="role_name_column">
                                        <label class="form-label position_name" for="position_name" style="display: none;">Role Name <span class="text-danger">*</span></label>
                                        <input type="text" name="position_name" id="position_name" class="form-control position_name" placeholder="Enter Role Here" value="<?=$position_name?>" style="display: none;" />
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="employer_id">Employer <span class="text-danger">*</span></label>
                                        <select class="select2" id="employer_id" name="employer_id" required>
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <?php if($employers){ foreach($employers as $select_row){?>
                                                <option value="<?=$select_row->id?>" <?=(($select_row->id == $employer_id)?'selected':'')?>><?=$select_row->name?></option>
                                            <?php } }?>
                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="job_type">Job Type <span class="text-danger">*</span></label>
                                        <select class="select2" id="job_type" name="job_type" required>
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <option value="walk-in-jobs" <?=(($job_type == 'walk-in-jobs')?'selected':'')?>>Walk-in</option>
                                            <option value="remote-jobs" <?=(($job_type == 'remote-jobs')?'selected':'')?>>Remote</option>
                                            <option value="on-site-jobs" <?=(($job_type == 'on-site-jobs')?'selected':'')?>>On-Site</option>
                                            <option value="temp-role-jobs" <?=(($job_type == 'temp-role-jobs')?'selected':'')?>>Temp Role</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="open_position_number">Number of open positions <span class="text-danger">*</span></label>
                                        <input type="number" name="open_position_number" id="open_position_number" class="form-control" min="1" value="<?=$open_position_number?>" required />
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="location_countries">Location Country <span class="text-danger">*</span></label>
                                        <select class="select2" id="location_countries" name="location_countries[]" required multiple>
                                            @foreach ($currencies as $select_row)
                                                <option value="{{ $select_row->id }}" 
                                                    {{ in_array($select_row->id, $location_countries ?? []) ? 'selected' : '' }}>
                                                    {{ $select_row->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="location_cities">Location City <span class="text-danger">*</span></label>
                                        <select class="select2" id="location_cities" name="location_cities[]" required multiple>
                                            <?php if($row){?>
                                                @foreach ($cities as $select_row)
                                                    <option value="{{ $select_row->id }}" 
                                                        {{ in_array($select_row->id, $location_cities ?? []) ? 'selected' : '' }}>
                                                        {{ $select_row->name }}
                                                    </option>
                                                @endforeach
                                            <?php }?>
                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="industry">Industry <span class="text-danger">*</span></label>
                                        <select class="select2" id="industry" name="industry" required>
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <?php if($industries){ foreach($industries as $select_row){?>
                                                <option value="<?=$select_row->id?>" <?=(($industry == $select_row->id)?'selected':'')?>><?=$select_row->name?></option>
                                            <?php } }?>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="job_category">Job Category <span class="text-danger">*</span></label>
                                        <select class="select2" id="job_category" name="job_category" required>
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <?php if($jobcats){ foreach($jobcats as $select_row){?>
                                                <option value="<?=$select_row->id?>" <?=(($job_category == $select_row->id)?'selected':'')?>><?=$select_row->name?></option>
                                            <?php } }?>
                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="nationality">Nationality <span class="text-danger">*</span></label>
                                        <select class="select2" id="nationality" name="nationality" required>
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <?php if($nationalities){ foreach($nationalities as $select_row){?>
                                                <option value="<?=$select_row->id?>" <?=(($nationality == $select_row->id)?'selected':'')?>><?=$select_row->name?></option>
                                            <?php } }?>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="gender">Gender <span class="text-danger">*</span></label>
                                        <select class="select2" id="gender" name="gender" required>
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <option value="Male" <?=(($gender == 'Male')?'selected':'')?>>Male</option>
                                            <option value="Female" <?=(($gender == 'Female')?'selected':'')?>>Female</option>
                                            <!-- <option value="Transgender" <?=(($gender == 'Transgender')?'selected':'')?>>Transgender</option> -->
                                            <option value="No Preference" <?=(($gender == 'No Preference')?'selected':'')?>>No Preference</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="contract_type">Contract Type <span class="text-danger">*</span></label>
                                        <select class="select2" id="contract_type" name="contract_type" required>
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <?php if($contract_types){ foreach($contract_types as $select_row){?>
                                                <option value="<?=$select_row->id?>" <?=(($contract_type == $select_row->id)?'selected':'')?>><?=$select_row->name?></option>
                                            <?php } }?>
                                        </select>
                                    </div>
                                    <!-- <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="designation">Designation</label>
                                        <select class="select2" id="designation" name="designation">
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <?php if($designations){ foreach($designations as $select_row){?>
                                                <option value="<?=$select_row->id?>" <?=(($designation == $select_row->id)?'selected':'')?>><?=$select_row->name?></option>
                                            <?php } }?>
                                        </select>
                                    </div> -->
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="functional_area">Functional Area</label>
                                        <select class="select2" id="functional_area" name="functional_area">
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <?php if($functionalareas){ foreach($functionalareas as $select_row){?>
                                                <option value="<?=$select_row->id?>" <?=(($functional_area == $select_row->id)?'selected':'')?>><?=$select_row->name?></option>
                                            <?php } }?>
                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="min_exp_year">Min Experience <span class="text-danger">*</span></label>
                                        <select class="select2" id="min_exp_year" name="min_exp_year">
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <?php for($i=0;$i<=25;$i++){?>
                                                <option value="<?=$i?>" <?=(($min_exp_year == $i)?'selected':'')?>><?=$i?> Years</option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="max_exp_year">Max Experience</label>
                                        <select class="select2" id="max_exp_year" name="max_exp_year">
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <?php for($i=0;$i<=25;$i++){?>
                                                <option value="<?=$i?>" <?=(($max_exp_year == $i)?'selected':'')?>><?=$i?> Years</option>
                                            <?php }?>
                                        </select>
                                    </div>

                                    <div class="col-12 d-flex justify-content-between">
                                        <button class="btn btn-label-secondary btn-prev" disabled>
                                            <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                            <span class="align-middle d-sm-inline-block d-none">Previous</span>
                                        </button>
                                        <button class="btn btn-primary btn-next" type="button">
                                            <span class="align-middle d-sm-inline-block d-none me-sm-2">Next</span>
                                            <i class="ti ti-arrow-right ti-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Personal Info -->
                            <div id="personal-info-validation" class="content">
                                <div class="content-header mb-4">
                                    <h6 class="mb-0">Step 2</h6>
                                    <small>Enter Your Step 2.</small>
                                </div>
                                <div class="row g-6 mt-3" id="step2">
                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label" for="job_description">Job Description</label>
                                        <textarea id="ckeditor1" name="job_description" class="form-control" placeholder="Job Description" rows="5"><?=$job_description?></textarea>
                                    </div>

                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label" for="requirement">Requirements</label>
                                        <textarea id="ckeditor2" name="requirement" class="form-control" placeholder="Requirements" rows="5"><?=$requirement?></textarea>
                                    </div>                                    

                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label" for="skill_ids">Skills</label>
                                        <select class="select2" id="skill_ids" name="skill_ids[]" required multiple>
                                            <?php if($keyskills){ foreach($keyskills as $select_row){?>
                                                <option value="<?=$select_row->id?>" <?=((in_array($select_row->id, $skill_ids))?'selected':'')?>><?=$select_row->name?></option>
                                            <?php } }?>
                                        </select>
                                    </div>
                                    
                                    <!-- <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="expected_close_date">Expected Close Date</label>
                                        <input type="date" name="expected_close_date" id="expected_close_date" class="form-control" placeholder="Expected Close Date" value="<?= !empty($expected_close_date) ? date('Y-m-d', strtotime($expected_close_date)) : '' ?>" min="<?=date('Y-m-d')?>" />
                                    </div> -->
                                    
                                    <div class="col-sm-12 mb-3">
                                        <label for="is_salary_negotiable" class="form-label d-block">Mark salary is negotiable</label>
                                        <div class="form-check form-switch mt-0 ">
                                            <input class="form-check-input" type="checkbox" name="is_salary_negotiable" role="switch" id="is_salary_negotiable" <?=(($is_salary_negotiable)?'checked':'')?>>
                                            <label class="form-check-label" for="is_salary_negotiable">YES</label>
                                        </div>
                                    </div>

                                    <div class="col-sm-4 mb-3 salary">
                                        <label class="form-label" for="currency">Currency</label>
                                        <select class="select2" id="currency" name="currency">
                                            <option label="" value="" selected disabled>Select an option</option>
                                            <?php if($currencies){ foreach($currencies as $select_row){?>
                                                <option value="<?=$select_row->currency_code?>" <?=(($currency == $select_row->currency_code)?'selected':'')?>><?=$select_row->currency_code?> (<?=$select_row->name?>)</option>
                                            <?php } }?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4 mb-3 salary">
                                        <label class="form-label" for="min_salary">Minimum Salary</label>
                                        <input type="text" name="min_salary" id="min_salary" class="form-control" placeholder="Minimum Salary" value="<?=$min_salary?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                                    </div>
                                    <div class="col-sm-4 mb-3 salary">
                                        <label class="form-label" for="max_salary">Maximum Salary</label>
                                        <input type="text" name="max_salary" id="max_salary" class="form-control" placeholder="Maximum Salary" value="<?=$max_salary?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                                    </div>                                    

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="posting_open_date">Posting Open Date</label>
                                        <input type="date" name="posting_open_date" id="posting_open_date" class="form-control" placeholder="Posting Open Date" value="<?= !empty($posting_open_date) ? date('Y-m-d', strtotime($posting_open_date)) : '' ?>" min="<?=date('Y-m-d')?>" />
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="posting_close_date">Posting Close Date</label>
                                        <input type="date" name="posting_close_date" id="posting_close_date" class="form-control" placeholder="Posting Close Date" value="<?= !empty($posting_close_date) ? date('Y-m-d', strtotime($posting_close_date)) : '' ?>" min="<?=date('Y-m-d')?>" max="<?=date('Y-m-d', strtotime('+30 days'))?>" />
                                    </div>

                                    <div class="col-12 d-flex justify-content-between">
                                        <button class="btn btn-primary btn-prev">
                                            <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                            <span class="align-middle d-sm-inline-block d-none">Previous</span>
                                        </button>
                                        <button class="btn btn-primary btn-next">
                                            <span class="align-middle d-sm-inline-block d-none me-sm-2">Next</span>
                                            <i class="ti ti-arrow-right ti-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Social Links -->
                            <div id="social-links-validation" class="content">
                                <div class="content-header mb-4">
                                    <h6 class="mb-0">Step 3</h6>
                                    <small>Enter Your Step 3.</small>
                                </div>
                                <div class="row g-6 mt-3" id="step3">
                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label" for="application_through">Accept Application Through</label><br>
                                        <input type="radio" name="application_through" id="application_through1" value="Hiring Jet" <?=(($application_through == 'Hiring Jet')?'checked':'')?> />
                                        <label for="application_through1">Hiring Jet</label>

                                        <input type="radio" name="application_through" id="application_through2" value="Apply To Email" <?=(($application_through == 'Apply To Email')?'checked':'')?> />
                                        <label for="application_through2">Apply To Email</label>

                                        <input type="radio" name="application_through" id="application_through3" value="Apply To Link" <?=(($application_through == 'Apply To Link')?'checked':'')?> />
                                        <label for="application_through3">Apply To Link</label>
                                    </div>
                                    <div class="col-sm-12 mb-3" id="apply_on_email_row" style="display:none;">
                                        <label class="form-label" for="apply_on_email">Apply To (Email)</label>
                                        <input type="text" name="apply_on_email" id="apply_on_email" class="form-control" placeholder="Apply To (Email)" value="<?=$apply_on_email?>" />
                                    </div>
                                    <div class="col-sm-12 mb-3" id="apply_on_link_row" style="display:none;">
                                        <label class="form-label" for="apply_on_link">Apply To (Link)</label>
                                        <input type="text" name="apply_on_link" id="apply_on_link" class="form-control" placeholder="Apply To (Link)" value="<?=$apply_on_link?>" />
                                    </div>

                                    <div class="col-sm-12">
                                        <h6>Office Address (for walk-ins)</h6>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <label class="form-label" for="walkin_details">Walkin Details</label>
                                        <textarea id="ckeditor3" name="walkin_details" class="form-control" placeholder="Walkin Details" rows="5"><?=$walkin_details?></textarea>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_address1">Address Line 1</label>
                                        <input type="text" name="walkin_address1" id="walkin_address1" class="form-control" placeholder="Address Line 1" value="<?=$walkin_address1?>" />
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_address2">Address Line 2</label>
                                        <input type="text" name="walkin_address2" id="walkin_address2" class="form-control" placeholder="Address Line 2" value="<?=$walkin_address2?>" />
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_country">Country</label>
                                        <input type="text" name="walkin_country" id="walkin_country" class="form-control" placeholder="Country" value="<?=$walkin_country?>" />
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_state">State</label>
                                        <input type="text" name="walkin_state" id="walkin_state" class="form-control" placeholder="State" value="<?=$walkin_state?>" />
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_city">City</label>
                                        <input type="text" name="walkin_city" id="walkin_city" class="form-control" placeholder="City" value="<?=$walkin_city?>" />
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="walkin_pincode">Pincode</label>
                                        <input type="text" name="walkin_pincode" id="walkin_pincode" class="form-control" placeholder="Pincode" value="<?=$walkin_pincode?>" />
                                        <input type="hidden" name="walkin_latitude" id="walkin_latitude" class="form-control" placeholder="Latitude" value="<?=$walkin_latitude?>" />
                                        <input type="hidden" name="walkin_longitude" id="walkin_longitude" class="form-control" placeholder="Longitude" value="<?=$walkin_longitude?>" />
                                    </div>

                                    <div class="col-12 d-flex justify-content-between">
                                        <button class="btn btn-primary btn-prev">
                                            <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                            <span class="align-middle d-sm-inline-block d-none">Previous</span>
                                        </button>
                                        <button type="submit" class="btn btn-success btn-next btn-submit">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    <!-- /Validation Wizard -->
                </div>
            </div>
        </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
    <!-- Vendors JS -->
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/select2/select2.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/@form-validation/popular.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/vendor/libs/@form-validation/auto-focus.js"></script>

    <!-- Page JS -->

    <script src="<?=config('constants.admin_assets_url')?>assets/js/form-wizard-numbered.js"></script>
    <script src="<?=config('constants.admin_assets_url')?>assets/js/form-wizard-validation.js"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBMbNCogNokCwVmJCRfefB6iCYUWv28LjQ&libraries=places&callback=initAutocomplete&libraries=places&v=weekly"></script>
    <script>
        $(document).ready(function () {
            var is_salary_negotiable = '<?=$is_salary_negotiable?>';
            if (is_salary_negotiable) {
                $('.salary').hide(); // Hide salary range if negotiable
            } else {
                $('.salary').show(); // Show salary range if not negotiable
            }
            $('#is_salary_negotiable').change(function () {
                if ($(this).is(':checked')) {
                    $('.salary').hide(); // Hide salary range if negotiable
                } else {
                    $('.salary').show(); // Show salary range if not negotiable
                }
            });

            var application_through = '<?=$application_through?>';
            // Hide all divs first
            $('#apply_on_email_row, #apply_on_link_row').hide();
            // Show based on selected value
            if (application_through === 'Hiring Jet') {
                $('#apply_on_email_row, #apply_on_link_row').hide();
            } else if (application_through === 'Apply To Email') {
                $('#apply_on_email_row').show();
                $('#apply_on_link_row').hide();
            } else if (application_through === 'Apply To Link') {
                $('#apply_on_email_row').hide();
                $('#apply_on_link_row').show();
            }
            $('input[name="application_through"]').change(function() {
                var application_through = $(this).val();

                // Hide all divs first
                $('#apply_on_email_row, #apply_on_link_row').hide();

                // Show based on selected value
                if (application_through === 'Hiring Jet') {
                    $('#apply_on_email_row, #apply_on_link_row').hide();
                } else if (application_through === 'Apply To Email') {
                    $('#apply_on_email_row').show();
                    $('#apply_on_link_row').hide();
                } else if (application_through === 'Apply To Link') {
                    $('#apply_on_email_row').hide();
                    $('#apply_on_link_row').show();
                }
            });

            var designation = '<?=$designation?>';
            if(designation == 7037){
                $('.position_name').show();
                $('#position_name').attr('required', true);
            } else {
                $('.position_name').hide();
                $('#position_name').attr('required', false);
            }
            $('#designation').on('change', function(){
                var designation = $('#designation').val();
                if(designation == 7037){
                    $('.position_name').show();
                    $('#position_name').attr('required', true);
                } else {
                    $('.position_name').hide();
                    $('#position_name').attr('required', false);
                }
            });
        });
        $(document).ready(function () {
            $('.select2').select2();

            let selectedCountries = @json($location_countries ?? []);
            let selectedCities = @json($location_cities ?? []);

            if (selectedCountries.length > 0) {
                $.ajax({
                    url: '{{ route("get.cities.by.countries") }}',
                    type: 'POST',
                    data: {
                        country_ids: selectedCountries,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function () {
                        $('#location_cities').prop('disabled', true);
                        $('#location_cities').html('<option>Loading...</option>');
                    },
                    success: function (data) {
                        let citySelect = $('#location_cities');
                        citySelect.empty();

                        $.each(data, function (index, city) {
                            let isSelected = selectedCities.includes(city.id);
                            //citySelect.append(`<option value="${city.id}" ${isSelected ? 'selected' : ''}>${city.name}</option>`);
                            citySelect.append(`<option value="${city.id}">${city.name}</option>`);
                        });

                        // citySelect.prop('disabled', false);
                        // citySelect.trigger('change'); // for select2 refresh

                        // Re-apply selected cities
                        citySelect.val(selectedCities).trigger('change');

                        citySelect.prop('disabled', false);
                    },
                    error: function () {
                        $('#location_cities').html('<option disabled>Error loading cities</option>');
                    }
                });
            }

            $('#location_countries').on('change', function () {
                let countryIds = $(this).val(); // get selected country IDs array
                
                $.ajax({
                    url: '{{ route("get.cities.by.countries") }}',
                    type: 'POST',
                    data: {
                        country_ids: countryIds,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function () {
                        // Show loading spinner or disable dropdown
                        $('#table-overlay-loader').show();
                    },
                    success: function (data) {
                        $('#table-overlay-loader').hide();
                        let citySelect = $('#location_cities');
                        citySelect.empty(); // clear previous

                        $.each(data, function (key, city) {
                            citySelect.append('<option value="'+ city.id +'">'+ city.name +'</option>');
                        });

                        citySelect.trigger('change'); // for select2
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openDate = document.getElementById('posting_open_date');
            const closeDate = document.getElementById('posting_close_date');

            // Function to format date to YYYY-MM-DD
            function formatDate(date) {
                const d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();
                return [year, month.padStart(2, '0'), day.padStart(2, '0')].join('-');
            }

            // Set initial min/max for close date on page load
            const today = new Date();
            const todayStr = formatDate(today);
            const plus7 = new Date(today);
            plus7.setDate(plus7.getDate() + 30);
            const plus7Str = formatDate(plus7);

            openDate.min = todayStr;
            closeDate.min = todayStr;
            closeDate.max = plus7Str;

            // When open date changes
            openDate.addEventListener('change', function() {
                const selectedOpenDate = new Date(this.value);
                if (isNaN(selectedOpenDate)) return;

                const newMin = formatDate(selectedOpenDate);
                const newMaxDate = new Date(selectedOpenDate);
                newMaxDate.setDate(newMaxDate.getDate() + 30);
                const newMax = formatDate(newMaxDate);

                closeDate.min = newMin;
                closeDate.max = newMax;

                // Optional: If current close date is out of new range, reset it
                if (closeDate.value < newMin || closeDate.value > newMax) {
                    closeDate.value = newMax;
                }
            });

            
        });
    </script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const dateFields = ['posting_open_date', 'posting_close_date'];

        dateFields.forEach(id => {
          const el = document.getElementById(id);
          if (el) {
            // Block all typing
            el.addEventListener('keydown', e => e.preventDefault());

            // Block pasting
            el.addEventListener('paste', e => e.preventDefault());

            // Fallback: clear value if manual input somehow sneaks in
            el.addEventListener('input', e => {
              const val = el.value;
              if (val && isNaN(new Date(val).getTime())) {
                el.value = '';
              }
            });
          }
        });
      });
    </script>
@endsection