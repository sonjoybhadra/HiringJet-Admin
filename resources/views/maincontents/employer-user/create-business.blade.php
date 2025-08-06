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
                    $first_name                 = $row->first_name;
                    $last_name                  = $row->last_name;
                    $email                      = $row->email;
                    $country_code               = $row->country_code;
                    $phone                      = $row->phone;
                    $business_id                = $row->business_id;
                    $designation_id             = $row->designation_id;
                } else {
                    $id                         = '';
                    $first_name                 = '';
                    $last_name                  = '';
                    $email                      = '';
                    $country_code               = '';
                    $phone                      = '';
                    $business_id                = '';
                    $designation_id             = '';
                }
                ?>
                <div class="card-body">
                    <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="address" class="form-label">Office Address <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="address" name="address" required placeholder="Office Address" autofocus />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country <small class="text-danger">*</small></label>
                                <select class="form-control" type="text" id="country" name="country_id" required>
                                    <option value="" selected>Select Country</option>
                                    <?php if ($countries) {
                                        foreach ($countries as $country) { ?>
                                            <option value="<?= $country->id ?>"><?= $country->name ?></option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State <small class="text-danger">*</small></label>
                                <select class="form-control" type="text" id="state" name="state_id" required>
                                    <option value="" selected>Select State</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City <small class="text-danger">*</small></label>
                                <select class="form-control" type="text" id="city" name="city_id" required>
                                    <option value="" selected>Select City</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pincode" class="form-label">Post Code <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="pincode" name="pincode" required placeholder="Post Code" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="landline" class="form-label">Office Landline <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="landline" name="landline" required placeholder="Office Landline" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="trade_license" class="form-label">Upload Trade License <small class="text-danger">*</small></label>
                                <input class="form-control" type="file" id="trade_license" name="trade_license" required placeholder="Upload Trade License" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="vat_registration" class="form-label">Upload VAT Registration <small class="text-danger">*</small></label>
                                <input class="form-control" type="file" id="vat_registration" name="vat_registration" required placeholder="Upload VAT Registration" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="logo" class="form-label">Upload Logo <small class="text-danger">*</small></label>
                                <input class="form-control" type="file" id="logo" name="logo" required placeholder="Upload Logo" />
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Brief Description <small class="text-danger">*</small></label>
                                <textarea class="form-control" id="description" name="description" required placeholder="Brief Description" rows="5"></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="industrie_id" class="form-label">Industry <small class="text-danger">*</small></label>
                                <select class="form-control" type="text" id="industrie_id" name="industrie_id" required>
                                    <option value="" selected>Select Industry</option>
                                    <?php if ($industries) {
                                        foreach ($industries as $industry) { ?>
                                            <option value="<?= $industry->id ?>"><?= $industry->name ?></option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="web_url" class="form-label">Website URL <small class="text-danger">*</small></label>
                                <input class="form-control" type="text" id="web_url" name="web_url" required placeholder="Website URL" />
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const stateDropdown = document.getElementById('state');
        const cityDropdown = document.getElementById('city');

        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'country') {
                const countryId = e.target.value;
                console.log('🟡 [Delegated] Country changed:', countryId);

                fetch("{{ route('get.states') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            country_id: countryId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        stateDropdown.innerHTML = '<option value="">Select State</option>';
                        for (let id in data) {
                            stateDropdown.innerHTML += `<option value="${id}">${data[id]}</option>`;
                        }
                        cityDropdown.innerHTML = '<option value="">Select City</option>';
                    })
                    .catch(error => console.error('❌ State fetch error:', error));
            }

            if (e.target && e.target.id === 'state') {
                const stateId = e.target.value;
                console.log('🟡 [Delegated] State changed:', stateId);

                fetch("{{ route('get.cities') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            state_id: stateId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        cityDropdown.innerHTML = '<option value="">Select City</option>';
                        for (let id in data) {
                            cityDropdown.innerHTML += `<option value="${id}">${data[id]}</option>`;
                        }
                    })
                    .catch(error => console.error('❌ City fetch error:', error));
            }
        });
    });
</script>
@endsection