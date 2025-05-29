@extends('layouts.auth')
@section('title', 'Sign In')
@section('content')
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
  <h4 class="mb-1"><?=$page_header?>? 🔒</h4>
  <p class="mb-6">Enter your email and we'll send you instructions to reset your password</p>
  <form id="formAuthentication" class="mb-6" action="{{ route('forgotpassword') }}" method="POST">
  	@csrf
    <div class="mb-6">
      <label for="email" class="form-label">Email</label>
      <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email" autofocus />
    </div>
    <button type="submit" class="btn btn-primary d-grid w-100">Send Reset Link</button>
  </form>
  <div class="mb-2 mb-md-0">
    © <script>document.write(new Date().getFullYear())</script>, Developed & maintained by <a href="https://itiffyconsultants.com/" target="_blank" class="footer-link fw-medium">Itiffy Consultants</a>
  </div>
  <div class="text-center">
    <a href="<?=url('/')?>" class="d-flex align-items-center justify-content-center">
      <i class="fa fa-chevron-left scaleX-n1-rtl me-1_5"></i>
      Back to login
    </a>
  </div>
</div>
@endsection