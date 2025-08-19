<?php
use App\Helpers\Helper;
$controllerRoute = $module['controller_route'];
use App\Models\PostJob;
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
            <div class="card-body">
               <form method="GET" action="">
                  @csrf
                  <input type="hidden" name="mode" value="search">
                  <div class="row mb-3" style="border: 1px solid #092b612b;padding: 10px;border-radius: 10px;">
                     <div class="col-lg-3 col-md-3">
                        <label for="search_day_id">Days</label>
                        <select name="search_day_id" class="form-control" id="search_day_id" required>
                         <option value="all" <?=(($search_day_id == 'all')?'selected':'')?>>All</option>
                         <hr>
                         <option value="today" <?=(($search_day_id == 'today')?'selected':'')?>>Today</option>
                         <hr>
                         <option value="yesterday" <?=(($search_day_id == 'yesterday')?'selected':'')?>>Yesterday</option>
                         <hr>
                         <option value="this_week" <?=(($search_day_id == 'this_week')?'selected':'')?>>This Week</option>
                         <hr>
                         <option value="last_week" <?=(($search_day_id == 'last_week')?'selected':'')?>>Last Week</option>
                         <hr>
                         <option value="this_month" <?=(($search_day_id == 'this_month')?'selected':'')?>>This Month</option>
                         <hr>
                         <option value="last_month" <?=(($search_day_id == 'last_month')?'selected':'')?>>Last Month</option>
                         <hr>
                         <option value="last_7_days" <?=(($search_day_id == 'last_7_days')?'selected':'')?>>Last 7 Days</option>
                         <hr>
                         <option value="last_30_days" <?=(($search_day_id == 'last_30_days')?'selected':'')?>>Last 30 Days</option>
                         <hr>
                         <option value="custom" <?=(($search_day_id == 'custom')?'selected':'')?>>Custom</option>
                         <hr>
                       </select>
                     </div>
                     <div class="col-lg-3 col-md-3 custom" style="display: none;">
                       <label for="search_range_from">From Date</label>
                       <input type="date" id="search_range_from" name="search_range_from" class="form-control" value="<?=$from_date?>" style="height: 40px;">
                     </div>
                     <div class="col-lg-3 col-md-3 custom" style="display: none;">
                       <label for="search_range_to">To Date</label>
                       <input type="date" id="search_range_to" name="search_range_to" class="form-control" value="<?=$to_date?>" style="height: 40px;">
                     </div>
                     <div class="col-lg-3 col-md-3">
                       <label for=""></label>
                       <button type="submit" class="btn btn-primary btn-sm" style="margin-top: 23px;"><i class="fa fa-paper-plane"></i>&nbsp;&nbsp;Generate</button>
                       <?php if($is_search){?>
                          <a href="<?=url('/post-job/user-wise-list')?>" class="btn btn-secondary btn-sm" style="margin-top: 23px;"><i class="fa fa-refresh"></i>&nbsp;&nbsp;Reset</a>
                       <?php }?>
                     </div>
                  </div>
               </form>
               <table id="simpletable" class="table table-striped table-bordered nowrap">
                  <thead>
                     <tr>
                     <th scope="col">#</th>
                     <th scope="col">Role</th>
                     <th scope="col">Name</th>
                     <th scope="col">Email</th>
                     <th scope="col">Country Code</th>
                     <th scope="col">Phone</th>
                     <th scope="col">Jobs</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if(count($subusers)>0){ $sl=1;$total_job=0; foreach($subusers as $subuser){?>
                           <tr>
                              <th scope="row"><?=$sl++?></th>
                              <td><?=$subuser->role_name?></td>
                              <td><?=$subuser->first_name.' '.$subuser->last_name?></td>
                              <td><?=$subuser->email?></td>
                              <td><?=$subuser->country_code?></td>
                              <td><?=$subuser->phone?></td>
                              <td style="text-align: right;">
                                <?php
                                if($from_date != '' && $to_date != ''){
                                    $job_count = PostJob::
                                                         where('status', '!=', 3)
                                                         ->where('created_by', '=', $subuser->id)
                                                         ->whereDate('created_at', '>=', $from_date)
                                                         ->whereDate('created_at', '<=', $to_date)
                                                         ->count();
                                } else {
                                    $job_count = PostJob::where('status', '!=', 3)->where('created_by', '=', $subuser->id)->count();
                                }
                                echo $job_count;
                                $total_job += $job_count;
                                ?>
                              </td>
                           </tr>
                     <?php } }?>
                  </tbody>
                  <tfoot>
                     <tr>
                        <th colspan="6" style="text-align: right; font-weight: bold;">TOTAL&nbsp;&nbsp;<i class="fa fa-arrow-right"></i></th>
                        <th style="text-align: right; font-weight: bold;"><?=$total_job?></th>
                     </tr>
                  </tfoot>
               </table>
            </div>
        </div>
      </div>
   </div>
</div>
@endsection

@section('scripts')
<script src="<?=config('constants.admin_assets_url')?>assets/js/table.js"></script>
<script>
   $(function(){
      $('.custom').hide();
      var search_day_id = '<?=$search_day_id?>';
      if(search_day_id == 'custom'){
         $('.custom').show();
         $('#search_range_from').attr('required', true);
         $('#search_range_to').attr('required', true);
      } else {
         $('.custom').hide();
         $('#search_range_from').attr('required', false);
         $('#search_range_to').attr('required', false);
      }
      $('#search_day_id').on('change', function(){
         var search_day_id = $('#search_day_id').val();
         if(search_day_id == 'custom'){
            $('.custom').show();
            $('#search_range_from').attr('required', true);
            $('#search_range_to').attr('required', true);
         } else {
            $('.custom').hide();
            $('#search_range_from').attr('required', false);
            $('#search_range_to').attr('required', false);
         }
      });
   })
</script>
@endsection