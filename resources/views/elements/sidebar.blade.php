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
  <li class="menu-item active <?=(($pageSegment == 'module' || $pageSegment == 'role' || $pageSegment == 'admin-user')?'open':'')?>">
    <a href="javascript:void(0);" class="menu-link menu-toggle">
      <i class="menu-icon fa-solid fa-lock"></i>
      <div data-i18n="Access & Permission">Access & Permission</div>
    </a>
    <ul class="menu-sub">
      <li class="menu-item <?=(($pageSegment == 'module')?'active':'')?>">
        <a href="<?=url('/module/list')?>" class="menu-link">
          <div data-i18n="Modules"><i class="fa-solid fa-arrow-right"></i> Modules</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'role')?'active':'')?>">
        <a href="<?=url('/role/list')?>" class="menu-link">
          <div data-i18n="Roles"><i class="fa-solid fa-arrow-right"></i> Roles</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'admin-user')?'active':'')?>">
        <a href="<?=url('/admin-user/list')?>" class="menu-link">
          <div data-i18n="Admin Users"><i class="fa-solid fa-arrow-right"></i> Admin Users</div>
        </a>
      </li>
    </ul>
  </li>
  <li class="menu-item active <?=(($pageSegment == 'industry' || $pageSegment == 'designation' || $pageSegment == 'keyskill' || $pageSegment == 'benefit' || $pageSegment == 'availability' || $pageSegment == 'university' || $pageSegment == 'most-common-email' || $pageSegment == 'language' || $pageSegment == 'religion' || $pageSegment == 'visa-status' || $pageSegment == 'marital-status' || $pageSegment == 'profile-complete' || $pageSegment == 'nationality' || $pageSegment == 'current-work-level' || $pageSegment == 'qualification' || $pageSegment == 'online-profile' || $pageSegment == 'employer' || $pageSegment == 'country' || $pageSegment == 'city' || $pageSegment == 'currency' || $pageSegment == 'course' || $pageSegment == 'specialization' || $pageSegment == 'job-category' || $pageSegment == 'functional-area' || $pageSegment == 'itskill')?'open':'')?>">
    <a href="javascript:void(0);" class="menu-link menu-toggle">
      <i class="menu-icon fa-solid fa-database"></i>
      <div data-i18n="Masters">Masters</div>
    </a>
    <ul class="menu-sub">
      <li class="menu-item <?=(($pageSegment == 'job-category')?'active':'')?>">
        <a href="<?=url('/job-category/list')?>" class="menu-link">
          <div data-i18n="Job Category"><i class="fa-solid fa-arrow-right"></i> Job Category</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'industry')?'active':'')?>">
        <a href="<?=url('/industry/list')?>" class="menu-link">
          <div data-i18n="Industry"><i class="fa-solid fa-arrow-right"></i> Industry</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'designation')?'active':'')?>">
        <a href="<?=url('/designation/list')?>" class="menu-link">
          <div data-i18n="Designation"><i class="fa-solid fa-arrow-right"></i> Designation</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'employer')?'active':'')?>">
        <a href="<?=url('/employer/list')?>" class="menu-link">
          <div data-i18n="Employer"><i class="fa-solid fa-arrow-right"></i> Employer</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'country')?'active':'')?>">
        <a href="<?=url('/country/list')?>" class="menu-link">
          <div data-i18n="Country"><i class="fa-solid fa-arrow-right"></i> Country</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'city')?'active':'')?>">
        <a href="<?=url('/city/list')?>" class="menu-link">
          <div data-i18n="City"><i class="fa-solid fa-arrow-right"></i> City</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'currency')?'active':'')?>">
        <a href="<?=url('/currency/list')?>" class="menu-link">
          <div data-i18n="Currency"><i class="fa-solid fa-arrow-right"></i> Currency</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'keyskill')?'active':'')?>">
        <a href="<?=url('/keyskill/list')?>" class="menu-link">
          <div data-i18n="Keyskill"><i class="fa-solid fa-arrow-right"></i> Keyskill</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'itskill')?'active':'')?>">
        <a href="<?=url('/itskill/list')?>" class="menu-link">
          <div data-i18n="IT skill"><i class="fa-solid fa-arrow-right"></i> IT skill</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'benefit')?'active':'')?>">
        <a href="<?=url('/benefit/list')?>" class="menu-link">
          <div data-i18n="Perk & Benefit"><i class="fa-solid fa-arrow-right"></i> Perk & Benefit</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'availability')?'active':'')?>">
        <a href="<?=url('/availability/list')?>" class="menu-link">
          <div data-i18n="Availability To Join"><i class="fa-solid fa-arrow-right"></i> Availability To Join</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'current-work-level')?'active':'')?>">
        <a href="<?=url('/current-work-level/list')?>" class="menu-link">
          <div data-i18n="Current Work Level"><i class="fa-solid fa-arrow-right"></i> Current Work Level</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'functional-area')?'active':'')?>">
        <a href="<?=url('/functional-area/list')?>" class="menu-link">
          <div data-i18n="Functional Area"><i class="fa-solid fa-arrow-right"></i> Functional Area</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'online-profile')?'active':'')?>">
        <a href="<?=url('/online-profile/list')?>" class="menu-link">
          <div data-i18n="Online Profile"><i class="fa-solid fa-arrow-right"></i> Online Profile</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'qualification')?'active':'')?>">
        <a href="<?=url('/qualification/list')?>" class="menu-link">
          <div data-i18n="Qualification"><i class="fa-solid fa-arrow-right"></i> Qualification</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'course')?'active':'')?>">
        <a href="<?=url('/course/list')?>" class="menu-link">
          <div data-i18n="Course"><i class="fa-solid fa-arrow-right"></i> Course</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'specialization')?'active':'')?>">
        <a href="<?=url('/specialization/list')?>" class="menu-link">
          <div data-i18n="Specialization"><i class="fa-solid fa-arrow-right"></i> Specialization</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'university')?'active':'')?>">
        <a href="<?=url('/university/list')?>" class="menu-link">
          <div data-i18n="University / Institute"><i class="fa-solid fa-arrow-right"></i> University / Institute</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'most-common-email')?'active':'')?>">
        <a href="<?=url('/most-common-email/list')?>" class="menu-link">
          <div data-i18n="Most Common Emails"><i class="fa-solid fa-arrow-right"></i> Most Common Emails</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'language')?'active':'')?>">
        <a href="<?=url('/language/list')?>" class="menu-link">
          <div data-i18n="Language Known"><i class="fa-solid fa-arrow-right"></i> Language Known</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'religion')?'active':'')?>">
        <a href="<?=url('/religion/list')?>" class="menu-link">
          <div data-i18n="Religion"><i class="fa-solid fa-arrow-right"></i> Religion</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'visa-status')?'active':'')?>">
        <a href="<?=url('/visa-status/list')?>" class="menu-link">
          <div data-i18n="Visa Status"><i class="fa-solid fa-arrow-right"></i> Visa Status</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'marital-status')?'active':'')?>">
        <a href="<?=url('/marital-status/list')?>" class="menu-link">
          <div data-i18n="Marital Status"><i class="fa-solid fa-arrow-right"></i> Marital Status</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'profile-complete')?'active':'')?>">
        <a href="<?=url('/profile-complete/list')?>" class="menu-link">
          <div data-i18n="Profile Complete Percentage"><i class="fa-solid fa-arrow-right"></i> Profile Complete Percentage</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'nationality')?'active':'')?>">
        <a href="<?=url('/nationality/list')?>" class="menu-link">
          <div data-i18n="Nationality"><i class="fa-solid fa-arrow-right"></i> Nationality</div>
        </a>
      </li>
    </ul>
  </li>
  <li class="menu-item active <?=(($pageSegment == 'faq-category' || $pageSegment == 'faq-sub-category' || $pageSegment == 'faq')?'open':'')?>">
    <a href="javascript:void(0);" class="menu-link menu-toggle">
      <i class="menu-icon fa-solid fa-circle-question"></i>
      <div data-i18n="FAQs">FAQs</div>
    </a>
    <ul class="menu-sub">
      <li class="menu-item <?=(($pageSegment == 'faq-category')?'active':'')?>">
        <a href="<?=url('/faq-category/list')?>" class="menu-link">
          <div data-i18n="FAQ Categories"><i class="fa-solid fa-arrow-right"></i> FAQ Categories</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'faq-sub-category')?'active':'')?>">
        <a href="<?=url('/faq-sub-category/list')?>" class="menu-link">
          <div data-i18n="FAQ Sub Categories"><i class="fa-solid fa-arrow-right"></i> FAQ Sub Categories</div>
        </a>
      </li>
      <li class="menu-item <?=(($pageSegment == 'faq')?'active':'')?>">
        <a href="<?=url('/faq/list')?>" class="menu-link">
          <div data-i18n="FAQs"><i class="fa-solid fa-arrow-right"></i> FAQs</div>
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
  <li class="menu-item <?=(($pageSegment == 'article')?'active':'')?>">
    <a href="<?=url('/article/list')?>" class="menu-link">
      <i class="menu-icon fa-solid fa-file-lines"></i>
      <div data-i18n="Articles">Articles</div>
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
  <li class="menu-item <?=(($pageSegment == 'user-activity-logs')?'active':'')?>">
    <a href="<?=url('/user-activity-logs')?>" class="menu-link">
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