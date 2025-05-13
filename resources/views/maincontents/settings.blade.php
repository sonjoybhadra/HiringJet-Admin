<?php
use App\Helpers\Helper;
$user_type = session('type');
?>
<h4><?=$page_header?></h4>
<h6 class="py-3 breadcrumb-wrapper mb-4">
   <span class="text-muted fw-light"><a href="<?=url('dashboard')?>">Dashboard</a> /</span> <?=$page_header?>
</h6>
<div class="row">
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
            <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-store align-top me-2"></i>Danger!</h6>
            <span><?=session('error_message')?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            </button>
         </div>
      <?php }?>
      <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
        <li class="nav-item">
          <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-profile" aria-controls="navs-pills-justified-profile" aria-selected="true"><i class="tf-icons bx bx-home me-1"></i> Profile</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-general" aria-controls="navs-pills-justified-general" aria-selected="false"><i class="tf-icons bx bx-user me-1"></i> General</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-password" aria-controls="navs-pills-justified-password" aria-selected="false"><i class="tf-icons bx bx-lock me-1"></i> Change Password</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-email" aria-controls="navs-pills-justified-email" aria-selected="false"><i class="tf-icons bx bx-envelope me-1"></i> Email</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-email-template" aria-controls="navs-pills-justified-email-template" aria-selected="false"><i class="tf-icons bx bx-message-square me-1"></i> Email Templates</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-payment" aria-controls="navs-pills-justified-payment" aria-selected="false"><i class="tf-icons bx bx-dollar me-1"></i> Payment</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-sms" aria-controls="navs-pills-justified-sms" aria-selected="false"><i class="tf-icons bx bx-mobile me-1"></i> SMS</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-footer" aria-controls="navs-pills-justified-footer" aria-selected="false"><i class="tf-icons bx bx-ball me-1"></i> Footer</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-justified-seo" aria-controls="navs-pills-justified-seo" aria-selected="false"><i class="tf-icons bx bx-line-chart me-1"></i> SEO</button>
        </li>
      </ul>
      <div class="tab-content">
         <div class="tab-pane fade show active" id="navs-pills-justified-profile" role="tabpanel">
            <h4>Profile Settings</h4>
            <div class="card mb-4">
               <div class="card-body">
                  <form id="formAccountSettings" action="<?=url('profile-settings')?>" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                        <div class="mb-3 col-md-4">
                           <label for="name" class="form-label">Name</label>
                           <input class="form-control" type="text" id="name" name="name" value="<?=(($user)?$user->name:'')?>" required autofocus />
                        </div>
                        <div class="mb-3 col-md-4">
                           <label for="email" class="form-label">Email</label>
                           <input class="form-control" type="text" id="email" name="email" value="<?=(($user)?$user->email:'')?>" required placeholder="john.doe@example.com" />
                        </div>
                        <div class="mb-3 col-md-4">
                           <label for="phone" class="form-label">Phone</label>
                           <input class="form-control" type="text" id="phone" name="phone" value="<?=(($user)?$user->phone:'')?>" required placeholder="9876543210" />
                        </div>
                        <div class="mb-3 col-md-12">
                           <div class="d-flex align-items-start align-items-sm-center gap-4">
                              <img src="<?=(($user->profile_image != '')?env('UPLOADS_URL').$user->profile_image:env('NO_IMAGE_AVATAR'))?>" alt="<?=$user->name?>" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
                              <div class="button-wrapper">
                                 <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                 <span class="d-none d-sm-block">Upload new photo</span>
                                 <i class="bx bx-upload d-block d-sm-none"></i>
                                 <input type="file" id="upload" class="account-file-input" name="profile_image" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                 </label>
                                 <?php
                                 $pageLink = Request::url();
                                 ?>
                                 <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/users/profile_image/id/' . (($user)?$user->id:0))?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                    <i class="bx bx-reset d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Reset</span>
                                 </a>
                                 <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                     </div>
                  </form>
               </div>
               <!-- /Account -->
            </div>
            <!-- <div class="card">
               <h5 class="card-header">Delete Account</h5>
               <div class="card-body">
                  <div class="mb-3 col-12 mb-0">
                     <div class="alert alert-warning">
                        <h6 class="alert-heading mb-1">Are you sure you want to delete your account?</h6>
                        <p class="mb-0">Once you delete your account, there is no going back. Please be certain.</p>
                     </div>
                  </div>
                  <form id="formAccountDeactivation" onsubmit="return false">
                     <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" />
                        <label class="form-check-label" for="accountActivation">I confirm my account deactivation</label>
                     </div>
                     <button type="submit" class="btn btn-danger deactivate-account">Deactivate Account</button>
                  </form>
               </div>
            </div> -->
         </div>
         <div class="tab-pane fade" id="navs-pills-justified-general" role="tabpanel">
            <h4>General Settings</h4>
            <div class="card mb-4">
               <div class="card-body">
                  <form id="formAccountSettings" action="<?=url('general-settings')?>" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                        <div class="mb-3 col-md-6">
                           <label for="site_name" class="form-label">Site Name</label>
                           <input class="form-control" type="text" id="site_name" name="site_name" value="<?=Helper::getSettingValue('site_name')?>" required placeholder="Site Name" autofocus />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="site_phone" class="form-label">Site Phone</label>
                           <input class="form-control" type="text" id="site_phone" name="site_phone" value="<?=Helper::getSettingValue('site_phone')?>" required placeholder="Site Phone" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="site_phone2" class="form-label">Site Phone 2</label>
                           <input class="form-control" type="text" id="site_phone2" name="site_phone2" value="<?=Helper::getSettingValue('site_phone2')?>" required placeholder="Site Phone 2" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="site_mail" class="form-label">Site Email</label>
                           <input class="form-control" type="email" id="site_mail" name="site_mail" value="<?=Helper::getSettingValue('site_mail')?>" required placeholder="SMTP Host" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="system_email" class="form-label">System Email</label>
                           <input class="form-control" type="email" id="system_email" name="system_email" value="<?=Helper::getSettingValue('system_email')?>" required placeholder="System Email" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="site_url" class="form-label">Site URL</label>
                           <input class="form-control" type="text" id="site_url" name="site_url" value="<?=Helper::getSettingValue('site_url')?>" placeholder="Site URL" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="topbar_text" class="form-label">Topbar Text</label>
                           <textraea class="form-control" id="ckeditor15" name="topbar_text" required><?=Helper::getSettingValue('topbar_text')?></textraea>
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="address" class="form-label">Address</label>
                           <textraea class="form-control" id="ckeditor8" name="address" required><?=Helper::getSettingValue('address')?></textraea>
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="description" class="form-label">Description</label>
                           <textraea class="form-control" id="ckeditor9" name="description" required><?=Helper::getSettingValue('description')?></textraea>
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="copyright_statement" class="form-label">Copyright Statement</label>
                           <textraea class="form-control" id="ckeditor10" name="copyright_statement" required><?=Helper::getSettingValue('copyright_statement')?></textraea>
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="google_map_api_code" class="form-label">Google Map API Code</label>
                           <textraea class="form-control" id="ckeditor11" name="google_map_api_code" required><?=Helper::getSettingValue('google_map_api_code')?></textraea>
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="google_analytics_code" class="form-label">Google Analytics Code</label>
                           <textraea class="form-control" id="ckeditor12" name="google_analytics_code" required><?=Helper::getSettingValue('google_analytics_code')?></textraea>
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="google_pixel_code" class="form-label">Google Pixel Code</label>
                           <textraea class="form-control" id="ckeditor13" name="google_pixel_code" required><?=Helper::getSettingValue('google_pixel_code')?></textraea>
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="facebook_tracking_code" class="form-label">Facebook Tracking Code</label>
                           <textraea class="form-control" id="ckeditor14" name="facebook_tracking_code" required><?=Helper::getSettingValue('facebook_tracking_code')?></textraea>
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="theme_color" class="form-label">Theme Color</label>
                           <input class="form-control" type="color" id="theme_color" name="theme_color" value="<?=Helper::getSettingValue('theme_color')?>" placeholder="Theme Color" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="font_color" class="form-label">Font Color</label>
                           <input class="form-control" type="color" id="font_color" name="font_color" value="<?=Helper::getSettingValue('font_color')?>" placeholder="Font Color" />
                        </div>

                        <div class="mb-3 col-md-6">
                           <label for="twitter_profile" class="form-label">Twitter Profile</label>
                           <input class="form-control" type="text" id="twitter_profile" name="twitter_profile" value="<?=Helper::getSettingValue('twitter_profile')?>" placeholder="Twitter Profile" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="facebook_profile" class="form-label">Facebook Profile</label>
                           <input class="form-control" type="text" id="facebook_profile" name="facebook_profile" value="<?=Helper::getSettingValue('facebook_profile')?>" placeholder="Facebook Profile" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="instagram_profile" class="form-label">Instagram Profile</label>
                           <input class="form-control" type="text" id="instagram_profile" name="instagram_profile" value="<?=Helper::getSettingValue('instagram_profile')?>" placeholder="Instagram Profile" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="linkedin_profile" class="form-label">Linkedin Profile</label>
                           <input class="form-control" type="text" id="linkedin_profile" name="linkedin_profile" value="<?=Helper::getSettingValue('linkedin_profile')?>" placeholder="Linkedin Profile" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="youtube_profile" class="form-label">Youtube Profile</label>
                           <input class="form-control" type="text" id="youtube_profile" name="youtube_profile" value="<?=Helper::getSettingValue('youtube_profile')?>" placeholder="Youtube Profile" />
                        </div>

                        <div class="mb-3 col-md-12">
                           <div class="d-flex align-items-start align-items-sm-center gap-4">
                              <img src="<?=((Helper::getSettingValue('site_logo') != '')?env('UPLOADS_URL').Helper::getSettingValue('site_logo'):env('NO_IMAGE'))?>" alt="<?=Helper::getSettingValue('site_name')?>" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
                              <div class="button-wrapper">
                                 <label for="upload2" class="btn btn-primary me-2 mb-4" tabindex="0">
                                 <span class="d-none d-sm-block">Upload Logo</span>
                                 <i class="bx bx-upload d-block d-sm-none"></i>
                                 <input type="file" id="upload2" class="account-file-input" name="site_logo" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                 </label>
                                 <?php
                                 $pageLink = Request::url();
                                 ?>
                                 <!-- <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/users/site_logo/id/' . (($user)?$user->id:0))?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                    <i class="bx bx-reset d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Reset</span>
                                 </a> -->
                                 <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                              </div>
                           </div>
                        </div>
                        <div class="mb-3 col-md-12">
                           <div class="d-flex align-items-start align-items-sm-center gap-4">
                              <img src="<?=((Helper::getSettingValue('site_footer_logo') != '')?env('UPLOADS_URL').Helper::getSettingValue('site_footer_logo'):env('NO_IMAGE'))?>" alt="<?=Helper::getSettingValue('site_name')?>" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
                              <div class="button-wrapper">
                                 <label for="upload3" class="btn btn-primary me-2 mb-4" tabindex="0">
                                 <span class="d-none d-sm-block">Upload Footer Logo</span>
                                 <i class="bx bx-upload d-block d-sm-none"></i>
                                 <input type="file" id="upload3" class="account-file-input" name="site_footer_logo" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                 </label>
                                 <?php
                                 $pageLink = Request::url();
                                 ?>
                                 <!-- <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/users/site_footer_logo/id/' . (($user)?$user->id:0))?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                    <i class="bx bx-reset d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Reset</span>
                                 </a> -->
                                 <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                              </div>
                           </div>
                        </div>
                        <div class="mb-3 col-md-12">
                           <div class="d-flex align-items-start align-items-sm-center gap-4">
                              <img src="<?=((Helper::getSettingValue('site_favicon') != '')?env('UPLOADS_URL').Helper::getSettingValue('site_favicon'):env('NO_IMAGE'))?>" alt="<?=Helper::getSettingValue('site_name')?>" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
                              <div class="button-wrapper">
                                 <label for="upload4" class="btn btn-primary me-2 mb-4" tabindex="0">
                                 <span class="d-none d-sm-block">Upload Favicon</span>
                                 <i class="bx bx-upload d-block d-sm-none"></i>
                                 <input type="file" id="upload4" class="account-file-input" name="site_favicon" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                 </label>
                                 <?php
                                 $pageLink = Request::url();
                                 ?>
                                 <!-- <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/users/site_favicon/id/' . (($user)?$user->id:0))?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                    <i class="bx bx-reset d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Reset</span>
                                 </a> -->
                                 <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                     </div>
                  </form>
               </div>
               <!-- /Account -->
            </div>
         </div>
         <div class="tab-pane fade" id="navs-pills-justified-password" role="tabpanel">
            <h4>Change Password</h4>
            <div class="card mb-4">
               <div class="card-body">
                  <form id="formAccountSettings" action="<?=url('change-password')?>" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                        <div class="mb-3 col-md-4">
                           <label for="old_password" class="form-label">Old Password</label>
                           <input class="form-control" type="password" id="old_password" name="old_password" placeholder="********" required autofocus />
                        </div>
                        <div class="mb-3 col-md-4">
                           <label for="new_password" class="form-label">New Password</label>
                           <input class="form-control" type="password" id="new_password" name="new_password" placeholder="********" required />
                        </div>
                        <div class="mb-3 col-md-4">
                           <label for="confirm_password" class="form-label">Confirm Password</label>
                           <input class="form-control" type="password" id="confirm_password" name="confirm_password" placeholder="********" required />
                        </div>
                     </div>
                     <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                     </div>
                  </form>
               </div>
               <!-- /Account -->
            </div>
         </div>
         <div class="tab-pane fade" id="navs-pills-justified-email" role="tabpanel">
            <h4>Email Configuration</h4>
            <div class="card mb-4">
               <div class="card-body">
                  <form id="formAccountSettings" action="<?=url('email-settings')?>" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                        <div class="mb-3 col-md-6">
                           <label for="from_email" class="form-label">From Email</label>
                           <input class="form-control" type="text" id="from_email" name="from_email" value="<?=Helper::getSettingValue('from_email')?>" required placeholder="From Email" autofocus />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="from_name" class="form-label">From Name</label>
                           <input class="form-control" type="text" id="from_name" name="from_name" value="<?=Helper::getSettingValue('from_name')?>" required placeholder="From Name" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="smtp_host" class="form-label">SMTP Host</label>
                           <input class="form-control" type="text" id="smtp_host" name="smtp_host" value="<?=Helper::getSettingValue('smtp_host')?>" required placeholder="SMTP Host" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="smtp_username" class="form-label">SMTP Username</label>
                           <input class="form-control" type="text" id="smtp_username" name="smtp_username" value="<?=Helper::getSettingValue('smtp_username')?>" required placeholder="SMTP Username" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="smtp_password" class="form-label">SMTP Password</label>
                           <input class="form-control" type="text" id="smtp_password" name="smtp_password" value="<?=Helper::getSettingValue('smtp_password')?>" required placeholder="SMTP Password" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="smtp_port" class="form-label">SMTP Port</label>
                           <input class="form-control" type="text" id="smtp_port" name="smtp_port" value="<?=Helper::getSettingValue('smtp_port')?>" required placeholder="SMTP Port" />
                        </div>
                     </div>
                     <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                     </div>
                  </form>
                  <p class="mt-3"><a href="<?=url('test-email')?>" class="btn btn-primary btn-sm"><i class="fa fa-envelope"></i>&nbsp;Send Test Email</a></p>
               </div>
               <!-- /Account -->
            </div>
         </div>
         <div class="tab-pane fade" id="navs-pills-justified-email-template" role="tabpanel">
            <h4>Email Templates</h4>
            <div class="card mb-4">
               <div class="card-body">
                  <form id="formAccountSettings" action="<?=url('email-template')?>" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                        <div class="mb-3 col-md-12">
                           <label for="email_template_user_signup" class="form-label">User Signup</label>
                           <textarea class="form-control" id="ckeditor4" name="email_template_user_signup" placeholder="User Signup"><?=Helper::getSettingValue('email_template_user_signup')?></textarea>
                        </div>
                        <div class="mb-3 col-md-12">
                           <label for="email_template_forgot_password" class="form-label">Forgot Password</label>
                           <textarea class="form-control" id="ckeditor5" name="email_template_forgot_password" placeholder="Forgot Password"><?=Helper::getSettingValue('email_template_forgot_password')?></textarea>
                        </div>
                        <div class="mb-3 col-md-12">
                           <label for="email_template_change_password" class="form-label">Change Password</label>
                           <textarea class="form-control" id="ckeditor6" name="email_template_change_password" placeholder="Change Password"><?=Helper::getSettingValue('email_template_change_password')?></textarea>
                        </div>
                        <div class="mb-3 col-md-12">
                           <label for="email_template_failed_login" class="form-label">Failed Login</label>
                           <textarea class="form-control" id="ckeditor7" name="email_template_failed_login" placeholder="Failed Login"><?=Helper::getSettingValue('email_template_failed_login')?></textarea>
                        </div>
                        <div class="mb-3 col-md-12">
                           <label for="email_template_contactus" class="form-label">Contact Us</label>
                           <textarea class="form-control" id="ckeditor17" name="email_template_contactus" placeholder="Contact Us"><?=Helper::getSettingValue('email_template_contactus')?></textarea>
                        </div>
                     </div>
                     <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                     </div>
                  </form>
               </div>
               <!-- /Account -->
            </div>
         </div>
         <div class="tab-pane fade" id="navs-pills-justified-payment" role="tabpanel">
            <h4>Payment Configuration</h4>
            <div class="card mb-4">
               <div class="card-body">
                  <form id="formAccountSettings" action="<?=url('payment-settings')?>" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                        <div class="mb-3 col-md-12">
                           <label for="stripe_payment_type" class="form-label">Stripe Payment Mode</label>
                           <select id="stripe_payment_type" class="select2 form-select" name="stripe_payment_type">
                              <option value="" selected>Select Stripe Payment Mode</option>
                              <option value="1" <?=((Helper::getSettingValue('stripe_payment_type') == 1)?'selected':'')?>>Sandbox</option>
                              <option value="2" <?=((Helper::getSettingValue('stripe_payment_type') == 2)?'selected':'')?>>Live</option>
                           </select>
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="stripe_sandbox_sk" class="form-label">Stripe Sandbox Secret Key</label>
                           <input class="form-control" type="text" id="stripe_sandbox_sk" name="stripe_sandbox_sk" value="<?=Helper::getSettingValue('stripe_sandbox_sk')?>" required placeholder="Stripe Sandbox Secret Key" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="stripe_sandbox_pk" class="form-label">Stripe Sandbox Public Key</label>
                           <input class="form-control" type="text" id="stripe_sandbox_pk" name="stripe_sandbox_pk" value="<?=Helper::getSettingValue('stripe_sandbox_pk')?>" required placeholder="Stripe Sandbox Public Key" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="stripe_live_sk" class="form-label">Stripe Live Secret Key</label>
                           <input class="form-control" type="text" id="stripe_live_sk" name="stripe_live_sk" value="<?=Helper::getSettingValue('stripe_live_sk')?>" required placeholder="Stripe Live Secret Key" />
                        </div>
                        <div class="mb-3 col-md-6">
                           <label for="stripe_live_pk" class="form-label">Stripe Live Public Key</label>
                           <input class="form-control" type="text" id="stripe_live_pk" name="stripe_live_pk" value="<?=Helper::getSettingValue('stripe_live_pk')?>" required placeholder="Stripe Live Public Key" />
                        </div>
                     </div>
                     <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                     </div>
                  </form>
               </div>
               <!-- /Account -->
            </div>
         </div>
         <div class="tab-pane fade" id="navs-pills-justified-sms" role="tabpanel">
            <h4>SMS Configuration</h4>
            <div class="card mb-4">
               <div class="card-body">
                  <form id="formAccountSettings" action="<?=url('sms-settings')?>" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                        <div class="mb-3 col-md-4">
                           <label for="sms_authentication_key" class="form-label">SMS Authentication Key</label>
                           <input class="form-control" type="text" id="sms_authentication_key" name="sms_authentication_key" value="<?=Helper::getSettingValue('sms_authentication_key')?>" required placeholder="SMS Authentication Key" autofocus />
                        </div>
                        <div class="mb-3 col-md-4">
                           <label for="sms_sender_id" class="form-label">SMS Sender ID</label>
                           <input class="form-control" type="text" id="sms_sender_id" name="sms_sender_id" value="<?=Helper::getSettingValue('sms_sender_id')?>" required placeholder="SMS Sender ID" />
                        </div>
                        <div class="mb-3 col-md-4">
                           <label for="sms_base_url" class="form-label">SMS Base URL</label>
                           <input class="form-control" type="text" id="sms_base_url" name="sms_base_url" value="<?=Helper::getSettingValue('sms_base_url')?>" required placeholder="SMS Base URL" />
                        </div>
                     </div>
                     <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                     </div>
                  </form>
               </div>
               <!-- /Account -->
            </div>
         </div>
         <div class="tab-pane fade" id="navs-pills-justified-footer" role="tabpanel">
            <h4>Footer Settings</h4>
            <div class="card mb-4">
               <div class="card-body">
                  <form id="formAccountSettings" action="<?=url('footer-settings')?>" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                        <div class="mb-3 col-md-12">
                           <label for="footer_text" class="form-label">Footer Text</label>
                           <textarea class="form-control" id="ckeditor18" name="footer_text" placeholder="Footer Text"><?=Helper::getSettingValue('footer_text')?></textarea>
                        </div>
                     </div>
                     <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                     </div>
                  </form>
               </div>
               <!-- /Account -->
            </div>
         </div>
         <div class="tab-pane fade" id="navs-pills-justified-seo" role="tabpanel">
            <h4>SEO Settings</h4>
            <div class="card mb-4">
               <div class="card-body">
                  <form id="formAccountSettings" action="<?=url('seo-settings')?>" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                        <div class="mb-3 col-md-12">
                           <label for="meta_title" class="form-label">Meta Title</label>
                           <textarea class="form-control" id="ckeditor1" name="meta_title" placeholder="Meta Title"><?=Helper::getSettingValue('meta_title')?></textarea>
                        </div>
                        <div class="mb-3 col-md-12">
                           <label for="meta_description" class="form-label">Meta Description</label>
                           <textarea class="form-control" id="ckeditor2" name="meta_description" placeholder="Meta Description"><?=Helper::getSettingValue('meta_description')?></textarea>
                        </div>
                        <div class="mb-3 col-md-12">
                           <label for="meta_keywords" class="form-label">Meta Keywords</label>
                           <textarea class="form-control" id="ckeditor3" name="meta_keywords" placeholder="Meta Keywords"><?=Helper::getSettingValue('meta_keywords')?></textarea>
                        </div>
                     </div>
                     <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                     </div>
                  </form>
               </div>
               <!-- /Account -->
            </div>
         </div>
      </div>
    </div>
</div>