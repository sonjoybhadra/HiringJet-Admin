@extends('layouts.auth')
@section('title', 'Sign In')
@section('content')
<?php
use App\Helpers\Helper;
use App\Models\Page;
$page_data                  = Page::where('page_slug', '=', 'terms-conditions')->first();
?>
<div class="mt-12 mt-5">
    <h4 class="mb-1"><?=$page_header?></h4>
    <!-- Render somewhere -->
    <div>
       {!! html_entity_decode((($page_data)?$page_data->page_content:'')) !!}
    </div>
</div>
@endsection