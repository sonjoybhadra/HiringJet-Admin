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
  <h4 class="mb-1"><?=$page_header?> 🔒</h4>
  <p class="mb-6">
    <span class="fw-medium">Your new password must be different from previously used passwords</span>
  </p>
  <form id="formAuthentication" class="mb-6" action="" method="POST">
  	@csrf
    <div class="mb-6 form-password-toggle">
      <label class="form-label" for="password">New Password</label>
      <div class="input-group input-group-merge">
        <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
        <span class="input-group-text cursor-pointer"><i class="fa-solid fa-eye-slash"></i></span>
      </div>
    </div>
    <div class="mb-6 form-password-toggle">
      <label class="form-label" for="confirm-password">Confirm Password</label>
      <div class="input-group input-group-merge">
        <input type="password" id="confirm-password" class="form-control" name="confirm-password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
        <span class="input-group-text cursor-pointer"><i class="fa-solid fa-eye-slash"></i></span>
      </div>
    </div>
    <button type="submit" class="btn btn-primary d-grid w-100 mb-6">Set new password</button>
    <div class="text-center">
      <a href="<?=url('/')?>">
        <i class="fa fa-chevron-left scaleX-n1-rtl me-1_5"></i>
        Back to login
      </a>
    </div>
  </form>
  <div class="mb-2 mb-md-0">
    © <script>document.write(new Date().getFullYear())</script>, Developed & maintained by <a href="https://itiffyconsultants.com/" target="_blank" class="footer-link fw-medium">Itiffy Consultants</a>
  </div>
</div>