<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendSimilarJobsAlert;

use App\Models\JobJobseekerSimilarJobsAlert;

class CronSchedularController extends Controller
{
    //

    public function sendSimilarJobsAlert(){
        $list = JobJobseekerSimilarJobsAlert::with('jobseeker')->take(2)->get();
        if($list->count() > 0){
            foreach($list as $value){
                $jobs = [];
                $jobseeket_name = $value->jobseeker->first_name.' '.$value->jobseeker->last_name;
                $email = $value->jobseeker->email;
                Mail::to('work.chayan2020@gmail.com')->send(new SendSimilarJobsAlert($jobseeket_name, $jobs));
            }
        }
    }
}
