<?php
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
?>
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
                $id                     = $row->id;
                $name                   = $row->name;
                $country_code           = $row->country_code;
                $country_flag           = $row->country_flag;
                $status                 = $row->status;
            } else {
                $id                     = '';
                $name                   = '';
                $country_code           = '';
                $country_flag           = '';
                $status                 = '';
            }
            ?>
            <div class="card-body">
                <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <label for="name" class="form-label">Name <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="name" name="name" value="<?=$name?>" required placeholder="Name" autofocus />
                        </div>
                        <div class="col-md-4">
                            <label for="country_code" class="form-label">Country ISD Code <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="country_code" name="country_code" value="<?=$country_code?>" required placeholder="Country ISD Code" autofocus />
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label d-block">Status <small class="text-danger">*</small></label>
                            <div class="form-check form-switch mt-0 ">
                                <input class="form-check-input" type="checkbox" name="status" role="switch" id="status" <?=(($status == 1)?'checked':'')?>>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start align-items-sm-center gap-4 mt-3">
                                <div class="button-wrapper">
                                    <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                        <span class="d-none d-sm-block">Upload Country Flag</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input type="file" id="upload" class="account-file-input" name="country_flag" hidden accept="image/png, image/jpeg, image/jpg, image/webp, image/avif, image/gif" />
                                    </label>
                                    <?php
                                    if(!empty($row)){
                                        $pageLink = Request::url();
                                    ?>
                                        <a href="<?=url('common-delete-image/' . Helper::encoded($pageLink) . '/countries/country_flag/id/' . $id)?>" class="btn btn-label-secondary account-image-reset mb-4" onclick="return confirm('Do you want to remove this image ?');">
                                            <i class="bx bx-reset d-block d-sm-none"></i>
                                            <span class="d-none d-sm-block">Reset</span>
                                        </a>
                                    <?php }?>
                                    <p class="mb-0">Allowed JPG, GIF, PNG, JPEG, WEBP, AVIF</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <img src="<?=(($country_flag != '')?$country_flag:env('NO_IMAGE'))?>" alt="<?=$name?>" class="img-thumbnail mt-3" height="200" width="200" id="uploadedAvatar" />
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