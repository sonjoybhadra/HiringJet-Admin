<?php

use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
@extends('layouts.main')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6">
        <h4><?= $page_header ?></h4>
        <h6 class="breadcrumb-wrapper">
            <span class="text-muted fw-light"><a href="<?= url('dashboard') ?>">Dashboard</a> /</span> <?= $page_header ?>
        </h6>
        <div class="nav-align-top mb-4">
            <?php if (session('success_message')) { ?>
                <div class="alert alert-success alert-dismissible autohide" role="alert">
                    <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-desktop align-top me-2"></i>Success!</h6>
                    <span><?= session('success_message') ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>
                </div>
            <?php } ?>
            <?php if (session('error_message')) { ?>
                <div class="alert alert-danger alert-dismissible autohide" role="alert">
                    <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-store align-top me-2"></i>Error!</h6>
                    <span><?= session('error_message') ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>
                </div>
            <?php } ?>
            <div class="card mb-4">
                <div class="card-header">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-5">
                                <label for="">Profile Percentage</label>
                                <select class="form-control" name="percentage_slot" id="percentage_slot" required>
                                    <option value="" selected>Select Profile Percentage</option>
                                    <option value="0-10">0% - 10%</option>
                                    <option value="11-20">11% - 20%</option>
                                    <option value="21-30">21% - 30%</option>
                                    <option value="31-40">31% - 40%</option>
                                    <option value="41-50">41% - 50%</option>
                                    <option value="51-60">51% - 60%</option>
                                    <option value="61-70">61% - 70%</option>
                                    <option value="71-80">71% - 80%</option>
                                    <option value="81-90">81% - 90%</option>
                                    <option value="91-100">91% - 100%</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="">Jobseeler Status</label>
                                <select class="form-control" name="status" id="status" required>
                                    <option value="all" selected>All</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2" style="margin-top: 23px;">
                                <button type="submit" class="btn btn-primary btn-sm me-2">Save Changes</button>
                                <?php if ($is_search) { ?>
                                    <a href="<?= url($controllerRoute . '/percentage-wise-list/') ?>" class="btn btn-label-secondary btn-sm">Reset</a>
                                <?php } ?>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="<?= config('constants.admin_assets_url') ?>assets/js/table.js"></script>
@endsection