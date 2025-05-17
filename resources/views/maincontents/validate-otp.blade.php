<?php
use App\Helpers\Helper;
?>
<div class="w-px-400 mx-auto mt-12 mt-5">
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
  <h4 class="mb-1"><?=$page_header?> 💬</h4>
  <p class="text-start mb-6">
    We sent a verification code to your email. Enter the code from the email in the field below.
    <span class="fw-medium d-block mt-1 text-heading">******@hiringjet.com</span>
  </p>
  <p class="mb-0">Type your 6 digit security code</p>
  <form id="twoStepsForm" action="" method="POST">
  	@csrf
    <div class="mb-6">
      <div class="auth-input-wrapper d-flex align-items-center justify-content-between numeral-mask-wrapper">
        <input type="tel" name="otp1" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1" autofocus />
        <input type="tel" name="otp2" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1" />
        <input type="tel" name="otp3" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1" />
        <input type="tel" name="otp4" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1" />
        <input type="tel" name="otp5" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1" />
        <input type="tel" name="otp6" class="form-control auth-input h-px-50 text-center numeral-mask mx-sm-1 my-2" maxlength="1" />
      </div>
      <!-- Create a hidden field which is combined by 3 fields above -->
      <input type="hidden" name="otp" />
    </div>
    <button type="submit" class="btn btn-primary d-grid w-100 mb-6">Verify my account</button>
    <div class="text-center">
      Didn't get the code?
      <a href="<?=url('resend-otp/' . Helper::encoded($id))?>"> Resend </a>
    </div>
  </form>
  <div class="mb-2 mb-md-0">
    © <script>document.write(new Date().getFullYear())</script>, Developed & maintained by <a href="https://itiffyconsultants.com/" target="_blank" class="footer-link fw-medium">Itiffy Consultants</a>
  </div>
</div>