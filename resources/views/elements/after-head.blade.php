<?php
use App\Helpers\Helper;
?>
<meta charset="utf-8" />
<meta
  name="viewport"
  content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

<title><?=$title?></title>

<meta name="title" content="<?=Helper::getSettingValue('meta_title')?>" />
<meta name="description" content="<?=Helper::getSettingValue('meta_description')?>" />
<meta name="keywords" content="<?=Helper::getSettingValue('meta_keywords')?>">
<meta name="base-url" content="<?=url('public/')?>">
<meta name="front-url" content="{{ config('constants.front_url') }}">
<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="<?=((Helper::getSettingValue('site_favicon') != '')?config('constants.app_url') . config('constants.uploads_url_path') . Helper::getSettingValue('site_favicon'):config('constants.no_image'))?>" />

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
  href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
  rel="stylesheet" />

<!-- Icons -->
<!-- <link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/fonts/fontawesome.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/fonts/tabler-icons.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/fonts/flag-icons.css" /> -->

<!-- Core CSS -->

<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/css/rtl/core.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/css/rtl/theme-default.css" />

<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/css/demo.css" />

<!-- Vendors CSS -->
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/node-waves/node-waves.css" />

<!-- posting job wizard -->
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/typeahead-js/typeahead.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/bs-stepper/bs-stepper.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/@form-validation/form-validation.css" />

<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/typeahead-js/typeahead.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/apex-charts/apex-charts.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/swiper/swiper.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" />

<!-- Page CSS -->
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/css/pages/cards-advance.css" />

<!-- Helpers -->
<script src="{{ config('constants.admin_assets_url') }}assets/vendor/js/helpers.js"></script>
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
<script src="{{ config('constants.admin_assets_url') }}assets/js/config.js"></script>

<link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/css/lightbox.min.css" rel="stylesheet">

<style>
  .pagination{
    float: right;
  }
  .pagination .page-btn{
    margin-right: 5px;
    border: 1px solid #092b61;
    background-color: #092b61;
    color: #FFF;
  }
  tbody tr td {
    font-size: 12px;
    padding: 5px;
  }
  .text-loader {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #FFFFFF;
    font-size: 1.5rem;
    font-weight: bold;
    backdrop-filter: blur(3px);
    text-shadow: 0 1px 3px rgba(0,0,0,0.4);
    font-family: 'Segoe UI', sans-serif;
  }
</style>