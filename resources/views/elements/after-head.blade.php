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

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="<?=((Helper::getSettingValue('site_favicon') != '')?env('UPLOADS_URL').Helper::getSettingValue('site_favicon'):env('NO_IMAGE'))?>" />

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
  href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
  rel="stylesheet" />

<!-- Icons -->
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/fonts/fontawesome.css" />
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/fonts/tabler-icons.css" />
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/fonts/flag-icons.css" />

<!-- Core CSS -->

<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/css/rtl/core.css" />
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/css/rtl/theme-default.css" />

<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/css/demo.css" />

<!-- Vendors CSS -->
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/libs/node-waves/node-waves.css" />

<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/libs/typeahead-js/typeahead.css" />
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/libs/apex-charts/apex-charts.css" />
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/libs/swiper/swiper.css" />
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" />

<!-- Page CSS -->
<link rel="stylesheet" href="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/css/pages/cards-advance.css" />

<!-- Helpers -->
<script src="<?=env('ADMIN_ASSETS_URL')?>assets/vendor/js/helpers.js"></script>
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
<script src="<?=env('ADMIN_ASSETS_URL')?>assets/js/config.js"></script>