<?php
use App\Helpers\Helper;
?>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

<title>@yield('title')</title>

<meta name="title" content="<?=Helper::getSettingValue('meta_title')?>" />
<meta name="description" content="<?=Helper::getSettingValue('meta_description')?>" />
<meta name="keywords" content="<?=Helper::getSettingValue('meta_keywords')?>">

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="<?=((Helper::getSettingValue('site_favicon') != '')?config('constants.app_url') . config('constants.uploads_url_path') . Helper::getSettingValue('site_favicon'):config('constants.no_image'))?>" />

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap" rel="stylesheet" />

<!-- Icons -->
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/fonts/fontawesome.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/fonts/tabler-icons.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/fonts/flag-icons.css" />

<!-- Core CSS -->

<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/css/rtl/core.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/css/rtl/theme-default.css" />

<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/css/demo.css" />

<!-- Vendors CSS -->
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/node-waves/node-waves.css" />

<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/typeahead-js/typeahead.css" />
<!-- Vendor -->
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/libs/@form-validation/form-validation.css" />

<!-- Page CSS -->
<!-- Page -->
<link rel="stylesheet" href="{{ config('constants.admin_assets_url') }}assets/vendor/css/pages/page-auth.css" />

<!-- Helpers -->
<script src="{{ config('constants.admin_assets_url') }}assets/vendor/js/helpers.js"></script>
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
<script src="{{ config('constants.admin_assets_url') }}assets/js/config.js"></script>
