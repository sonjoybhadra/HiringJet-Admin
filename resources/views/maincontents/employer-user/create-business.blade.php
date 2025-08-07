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
        address1Field = document.querySelector("#address");
        address2Field = document.querySelector("#walkin_address2");
        postalField = document.querySelector("#walkin_pincode");
        autocomplete = new google.maps.places.Autocomplete(address1Field, {
            componentRestrictions: {
                country: []
            },
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
        <h4><?= $page_header ?></h4>
        <h6 class="breadcrumb-wrapper">
            <span class="text-muted fw-light"><a href="<?= url('dashboard') ?>">Dashboard</a> /</span>
            <span class="text-muted fw-light"><a href="<?= url($controllerRoute . '/list/') ?>"><?= $module['title'] ?> List</a> /</span>
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
                <?php
                if ($row) {
                    $id                         = $row->id;
                    $address                 = $row->address;
                    $country_id                  = $row->country_id;
                    $state_id                      = $row->state_id;
                    $city_id               = $row->city_id;
                    $pincode                      = $row->pincode;
                    $landline                = $row->landline;
                    $trade_license             = $row->trade_license;
                    $vat_registration             = $row->vat_registration;
                    $logo             = $row->logo;
                    $description             = $row->description;
                    $web_url             = $row->web_url;
                    $country_code             = $row->country_code;
                    $industrie_id             = $row->industrie_id;
                } else {
                    $id                         = '';
                    $address                 = '';
                    $country_id                  = '';
                    $state_id                      = '';
                    $city_id               = '';
                    $pincode                      = '';
                    $landline                = '';
                    $trade_license             = '';
                    $vat_registration             = '';
                    $logo             = '';
                    $description             = '';
                    $web_url             = '';
                    $country_code             = '';
                    $industrie_id             = '';
                }
                ?>
                <div class="card-body">
                    <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="address" class="form-label">Office Address <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="address" name="address" value="<?=$address?>" required placeholder="Office Address" autofocus />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country <small class="text-danger">*</small></label>
                                <select class="form-control" type="text" id="country" name="country" required>
                                    <option value="" selected>Select Country</option>
                                    <?php if ($countries) {
                                        foreach ($countries as $country) { ?>
                                            <option value="<?= $country->id ?>" <?=(($country_id == $country->id)?'selected':'')?>><?= $country->name ?></option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State <small class="text-danger">*</small></label>
                                <select class="form-control" type="text" id="state" name="state" required>
                                    <option value="" selected>Select State</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City <small class="text-danger">*</small></label>
                                <select class="form-control" type="text" id="city" name="city" required>
                                    <option value="" selected>Select City</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pincode" class="form-label">Post Code <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="pincode" name="pincode" required value="<?=$pincode?>" placeholder="Post Code" />
                            </div>
                            
                            <div class="col-md-2 mb-3">
                                <label for="country_code" class="form-label">Country Code <small class="text-danger">*</small></label>
                                <select class="form-control" type="text" id="country_code" name="country_code" required>
                                    <option value="" selected>Select Country</option>
                                    <?php if ($countries) {
                                        foreach ($countries as $country) { ?>
                                            <option value="<?= $country->country_code ?>" <?=(($country_code == $country->country_code)?'selected':'')?>><?= $country->country_code ?> (<?= $country->name ?>)</option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="landline" class="form-label">Office Landline <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="landline" name="landline" required placeholder="Office Landline" value="<?=$landline?>" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="trade_license" class="form-label">Upload Trade License <small class="text-danger">*</small></label>
                                <input class="form-control" type="file" id="trade_license" name="trade_license" required placeholder="Upload Trade License" />
                                <?php if($trade_license){?>
                                    <p><img src="<?=url('/').'/'.$trade_license?>" class="img-thumbnail" style="width:100px; height:100px;"></p>
                                <?php }?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="vat_registration" class="form-label">Upload VAT Registration <small class="text-danger">*</small></label>
                                <input class="form-control" type="file" id="vat_registration" name="vat_registration" required placeholder="Upload VAT Registration" />
                                <?php if($vat_registration){?>
                                    <p><img src="<?=url('/').'/'.$vat_registration?>" class="img-thumbnail" style="width:100px; height:100px;"></p>
                                <?php }?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="logo" class="form-label">Upload Logo <small class="text-danger">*</small></label>
                                <input class="form-control" type="file" id="logo" name="logo" required placeholder="Upload Logo" />
                                <?php if($logo){?>
                                    <p><img src="<?=url('/').'/'.$logo?>" class="img-thumbnail" style="width:100px; height:100px;"></p>
                                <?php }?>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Brief Description <small class="text-danger">*</small></label>
                                <textarea class="form-control" id="description" name="description" required placeholder="Brief Description" rows="5"><?=$description?></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="industrie_id" class="form-label">Industry <small class="text-danger">*</small></label>
                                <select class="form-control" type="text" id="industrie_id" name="industrie_id" required>
                                    <option value="" selected>Select Industry</option>
                                    <?php if ($industries) {
                                        foreach ($industries as $industry) { ?>
                                            <option value="<?= $industry->id ?>" <?=(($industrie_id == $industry->id)?'selected':'')?>><?= $industry->name ?></option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="web_url" class="form-label">Website URL <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="web_url" name="web_url" value="<?=$web_url?>" required placeholder="Website URL" />
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary btn-sm me-2">Save Changes</button>
                            <a href="<?= url($controllerRoute . '/list/') ?>" class="btn btn-label-secondary btn-sm">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBMbNCogNokCwVmJCRfefB6iCYUWv28LjQ&libraries=places&callback=initAutocomplete&libraries=places&v=weekly"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // console.log("✅ jQuery loaded and ready");

        // $.ajaxSetup({
        //     headers: {
        //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //     }
        // });

        $('#country').on('change', function() {
            let countryId = $(this).val();
            // console.log("🟡 Country changed:", countryId);

            if (countryId) {
                $.post("{{ route('get.states') }}", {
                    country_id: countryId,
                    _token: '{{ csrf_token() }}'
                }, function(data) {
                    $('#state').empty().append('<option value="">Select State</option>');
                    $('#city').empty().append('<option value="">Select City</option>');
                    $.each(data, function(id, name) {
                        $('#state').append('<option value="' + id + '">' + name + '</option>');
                    });
                }).fail(function(xhr, status, error) {
                    console.error("❌ Error loading states:", error);
                });
            }
        });

        $('#country').on('change', function() {
            let countryId = $(this).val();
            // console.log("🟡 State changed:", stateId);

            if (countryId) {
                $.post("{{ route('get.cities') }}", {
                    country_id: countryId,
                    _token: '{{ csrf_token() }}'
                }, function(data) {
                    $('#city').empty().append('<option value="">Select City</option>');
                    $.each(data, function(id, name) {
                        $('#city').append('<option value="' + id + '">' + name + '</option>');
                    });
                }).fail(function(xhr, status, error) {
                    console.error("❌ Error loading cities:", error);
                });
            }
        });
    });
</script>
@endsection