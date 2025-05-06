<?php
use App\Helpers\Helper;
?>
<div class="w-px-400 mx-auto mt-12 pt-5">
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
  <h4 class="mb-1">Welcome to <?=Helper::getSettingValue('site_name')?>! 👋</h4>
  <p class="mb-6">Please sign-in to your account and start the adventure</p>

  <form class="mb-6" action="{{ route('signin') }}" method="POST">
    @csrf
    <div class="mb-6">
      <label for="email" class="form-label">Email or Username</label>
      <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email or username" autofocus />
    </div>
    <div class="mb-6 form-password-toggle">
      <label class="form-label" for="password">Password</label>
      <div class="input-group input-group-merge">
        <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
        <span class="input-group-text cursor-pointer"><i class="fa-solid fa-eye-slash"></i></span>
      </div>
    </div>
    <div class="my-8">
      <div class="d-flex justify-content-between">
        <div class="form-check mb-0 ms-2">
          <!-- <input class="form-check-input" type="checkbox" id="remember-me" />
          <label class="form-check-label" for="remember-me"> Remember Me </label> -->
        </div>
        <a href="<?=url('forgot-password')?>">
          <p class="mb-0">Forgot Password?</p>
        </a>
      </div>
    </div>
    <button type="submit" class="btn btn-primary d-grid w-100">Sign in</button>
  </form>
  <div class="mb-2 mb-md-0">
    © <script>document.write(new Date().getFullYear())</script>, developed & maintained by <a href="https://itiffyconsultants.com/" target="_blank" class="footer-link fw-medium">Itiffy Consultants</a>
  </div>
  <!-- <p class="text-center">
    <span>New on our platform?</span>
    <a href="auth-register-cover.html">
      <span>Create an account</span>
    </a>
  </p>

  <div class="divider my-6">
    <div class="divider-text">or</div>
  </div>

  <div class="d-flex justify-content-center">
    <a href="<?=Helper::getSettingValue('facebook_profile')?>" target="_blank" class="btn btn-sm btn-icon rounded-pill btn-text-facebook me-1_5">
      <i class="tf-icons fa-brands fa-facebook-f"></i>
    </a>

    <a href="<?=Helper::getSettingValue('twitter_profile')?>" target="_blank" class="btn btn-sm btn-icon rounded-pill btn-text-twitter me-1_5">
      <i class="tf-icons fa-brands fa-twitter"></i>
    </a>

    <a href="<?=Helper::getSettingValue('instagram_profile')?>" target="_blank" class="btn btn-sm btn-icon rounded-pill btn-text-github me-1_5">
      <i class="tf-icons fa-brands fa-github"></i>
    </a>

    <a href="<?=Helper::getSettingValue('youtube_profile')?>" target="_blank" class="btn btn-sm btn-icon rounded-pill btn-text-google-plus">
      <i class="tf-icons fa-brands fa-youtube"></i>
    </a>
  </div> -->
</div>