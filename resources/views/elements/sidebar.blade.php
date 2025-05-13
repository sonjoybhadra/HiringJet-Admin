<?php
use App\Helpers\Helper;
use Illuminate\Support\Facades\Route;
$routeName    = Route::current();
$pageName     = explode("/", $routeName->uri());
$pageSegment  = $pageName[0];
$pageFunction = ((count($pageName)>1)?$pageName[1]:'');
?>
<div class="app-brand demo">
  <a href="<?=url('/dashboard')?>" class="app-brand-link">
    <!-- <span class="app-brand-logo demo">
      <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
          fill="#7367F0" />
        <path
          opacity="0.06"
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
          fill="#161616" />
        <path
          opacity="0.06"
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
          fill="#161616" />
        <path
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
          fill="#7367F0" />
      </svg>
    </span> -->
    <img src="<?=((Helper::getSettingValue('site_logo') != '')?env('UPLOADS_URL').Helper::getSettingValue('site_logo'):env('NO_IMAGE'))?>" alt="<?=Helper::getSettingValue('site_name')?>" class="d-block" style="margin-top: 10px;height: 50px;width: 150px;" />
    <!-- <span class="app-brand-text demo menu-text fw-bold"><?=Helper::getSettingValue('site_name')?></span> -->
  </a>

  <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
    <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
    <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
  </a>
</div>

<div class="menu-inner-shadow"></div>

<ul class="menu-inner py-1">
  <!-- Dashboards -->
  <li class="menu-item <?=(($pageSegment == 'dashboard')?'active':'')?>">
    <a href="<?=url('/dashboard')?>" class="menu-link">
      <i class="menu-icon fa-solid fa-house"></i>
      <div data-i18n="Dashboard">Dashboard</div>
    </a>
  </li>
  <li class="menu-item active <?=(($pageSegment == 'faq-category' || $pageSegment == 'faq')?'open':'')?>">
    <a href="javascript:void(0);" class="menu-link menu-toggle">
      <i class="menu-icon fa-solid fa-circle-question"></i>
      <div data-i18n="FAQs">FAQs</div>
    </a>
    <ul class="menu-sub">
      <li class="menu-item <?=(($pageSegment == 'faq-category')?'active':'')?>">
        <a href="<?=url('/faq-category/list')?>" class="menu-link">
          <div data-i18n="FAQ Categories">FAQ Categories</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'faq')?'active':'')?>">
        <a href="<?=url('/faq/list')?>" class="menu-link">
          <div data-i18n="FAQs">FAQs</div>
        </a>
      </li>
    </ul>
  </li>
  <li class="menu-item <?=(($pageSegment == 'page')?'active':'')?>">
    <a href="<?=url('/page/list')?>" class="menu-link">
      <i class="menu-icon fa-solid fa-file-lines"></i>
      <div data-i18n="CMS Pages">CMS Pages</div>
    </a>
  </li>
  <li class="menu-item <?=(($pageSegment == 'email-logs')?'active':'')?>">
    <a href="<?=url('/email-logs')?>" class="menu-link">
      <i class="menu-icon fa-solid fa-envelope"></i>
      <div data-i18n="Email Logs">Email Logs</div>
    </a>
  </li>
  <li class="menu-item <?=(($pageSegment == 'login-logs')?'active':'')?>">
    <a href="<?=url('/login-logs')?>" class="menu-link">
      <i class="menu-icon fa-solid fa-right-to-bracket"></i>
      <div data-i18n="Login Logs">Login Logs</div>
    </a>
  </li>
  <li class="menu-item <?=(($pageSegment == 'login-logs')?'active':'')?>">
    <a href="<?=url('/login-logs')?>" class="menu-link">
      <i class="menu-icon fa-solid fa-chart-line"></i>
      <div data-i18n="User Activity Logs">User Activity Logs</div>
    </a>
  </li>
  <li class="menu-item <?=(($pageSegment == 'settings')?'active':'')?>">
    <a href="<?=url('/settings')?>" class="menu-link">
      <i class="menu-icon fa-solid fa-gear"></i>
      <div data-i18n="Settings">Settings</div>
    </a>
  </li>
  <li class="menu-item">
    <a href="<?=url('/logout')?>" class="menu-link">
      <i class="menu-icon fa-solid fa-arrow-right-from-bracket"></i>
      <div data-i18n="Log Out">Log Out</div>
    </a>
  </li>
</ul>