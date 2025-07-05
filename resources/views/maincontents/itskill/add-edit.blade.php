<?php
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
?>
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
                $name                       = $row->name;
                $version                    = $row->version;
                $publishing_year            = $row->publishing_year;
                $status                     = $row->status;
            } else {
                $name                       = '';
                $version                    = '';
                $publishing_year            = '';
                $status                     = '';
            }
            ?>
            <div class="card-body">
                <form id="formAccountSettings" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="name" name="name" value="<?=$name?>" required placeholder="Name" autofocus />
                        </div>
                        <div class="col-md-6">
                            <label for="version" class="form-label">Version <small class="text-danger">*</small></label>
                            <input type="text" name="input-tags" class="form-control" id="input-tags">
                            <div id="validation-msg" style="color:red; font-size: 0.9em;"></div>
                            <div id="input-tags-error" class="error"></div>

                            <textarea class="form-control" name="version" id="version" style="display:none;"><?=$version?></textarea>
                            <small class="text-primary">Type a comma after each version</small>
                            <div id="badge-container">
                                <?php
                                if($version != ''){
                                    $deal_keywords = explode(",", $version);
                                    if(!empty($deal_keywords)){
                                    for($k=0;$k<count($deal_keywords);$k++){
                                ?>
                                    <span class="badge" style="background-color: #092b61; margin-right:5px;"><?=$deal_keywords[$k]?> <span class="remove" data-tag="<?=$deal_keywords[$k]?>">&times;</span></span>
                                <?php } }
                                }
                                ?>
                            </div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label for="publishing_year" class="form-label">Publishing Year <small class="text-danger">*</small></label>
                            <input class="form-control" type="text" id="publishing_year" name="publishing_year" value="<?=$publishing_year?>" required placeholder="Publishing Year" />
                        </div>
                        <div class="col-md-6 mt-3">
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
<script type="text/javascript">
    $(document).ready(function () {
        var tagsArray = [];
        var beforeData = $('#version').val();
        if (beforeData.length > 0) {
            tagsArray = beforeData.split(',');
        }

        $('#input-tags').on('input', function () {
            var input = $(this).val().trim();
            if (input.length > 0) {
                $(this).removeAttr('required'); // remove required
                // $('#input-tags-error').hide();  // hide any previous error
            }
            $('#validation-msg').text('').hide();  // 🛠️ Hide old error immediately
            // When comma is typed
            if (input.includes(',')) {
                var tags = input.split(',');
                tags.forEach(function (tag) {
                    tag = tag.trim();
                    if (tag.length > 0) {   
                        if (!tagsArray.includes(tag)) {
                            tagsArray.push(tag);
                            $('#badge-container').append(
                                '<span class="badge" style="background-color: #092b61; margin-right:5px;">' + tag + ' <span class="remove" data-tag="' + tag + '">&times;</span></span>'
                            );
                        }
                    }
                });
                $('#version').val(tagsArray.join(','));
                $(this).val('');
            }
        });

        // Remove tag handler
        $(document).on('click', '.remove', function () {
            var tag = $(this).data('tag');
            tagsArray = tagsArray.filter(function (item) {
                return item !== tag;
            });
            $(this).parent().remove();
            $('#version').val(tagsArray.join(','));
        });
    });
</script>
@endsection