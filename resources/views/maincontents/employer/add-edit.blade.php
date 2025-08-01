<?php
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
?>
<!-- Include jQuery and jQuery UI CSS/JS -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
@extends('layouts.main')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
   <div class="row g-6">
      <h4><?=$page_header?></h4>
      <h6 class="breadcrumb-wrapper">
            <span class="text-muted fw-light"><a href="<?=url('dashboard')?>">Dashboard</a> /</span>
            <span class="text-muted fw-light"><a href="<?=url($controllerRoute . '/list/')?>"><?=$module['title']?> List</a> /</span>
            <?=$page_header?>
      </h6>
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
               <h6 class="alert-heading mb-1"><i class="bx bx-xs bx-store align-top me-2"></i>Error!</h6>
               <span><?=session('error_message')?></span>
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
               </button>
            </div>
         <?php }?>
         <div class="card mb-4">
            <?php
            if($row){
                $id                 = $row->id;
                $name               = $row->name;
                $description        = $row->description;
                $industry_id        = $row->industry_id;
                $no_of_employee     = $row->no_of_employee;
                $logo               = $row->logo;
                $status             = $row->status;
            } else {
                $id                 = '';
                $name               = '';
                $description        = '';
                $industry_id        = '';
                $no_of_employee     = '';
                $logo               = '';
                $status             = '';
            }
            ?>
            <div class="card-body">
                <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="employer" name="name" value="<?=$name?>" required placeholder="Name" autofocus />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="industry_id" class="form-label">Industry <small class="text-danger">*</small></label>
                            <select class="form-control" type="text" id="industry_id" name="industry_id" required>
                                <option value="" selected>Select Industry</option>
                                <?php if($industries){ foreach($industries as $industry){?>
                                    <option value="<?=$industry->id?>" <?=(($industry->id == $industry_id)?'selected':'')?>><?=$industry->name?></option>
                                <?php } }?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" placeholder="Description"><?=$description?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="no_of_employee" class="form-label">No. Of Employee</label>
                            <input class="form-control" type="text" id="no_of_employee" name="no_of_employee" value="<?=$no_of_employee?>" placeholder="No. Of Employee" autofocus />
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-start align-items-sm-center gap-4 mt-3">
                                <div class="button-wrapper">
                                    <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                        <span class="d-none d-sm-block">Upload Logo</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input type="file" id="upload" class="account-file-input" name="logo" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                    </label>
                                    <?php
                                    if(!empty($row)){
                                        $pageLink = Request::url();
                                    ?>
                                        <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/employers/logo/id/' . $id)?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                            <i class="bx bx-reset d-block d-sm-none"></i>
                                            <span class="d-none d-sm-block">Reset</span>
                                        </a>
                                    <?php }?>
                                    <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <img src="<?=(($logo != '')?url('public/') . $logo:config('constants.no_image'))?>" alt="<?=$name?>" class="img-thumbnail mt-3" height="200" width="200" id="uploadedAvatar" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label d-block">Status <small class="text-danger">*</small></label>
                            <div class="form-check form-switch mt-0 ">
                                <input class="form-check-input" type="checkbox" name="status" role="switch" id="status" <?=(($status == 1)?'checked':'')?>>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary btn-sm me-2">Save Changes</button>
                        <a href="<?=url($controllerRoute . '/list/')?>" class="btn btn-label-secondary btn-sm">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
    <script>
        $(function () {
            $('#employer').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('employers.suggest') }}",
                        data: { term: request.term },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 1
            });
        });
    </script>
@endsection