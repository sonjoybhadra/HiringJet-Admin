<?php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Mail\NotificationEmail;

use App\Models\PostJob;
use App\Models\PostJobUserApplied;
use App\Models\ShortlistedJob;
use App\Models\UserEmployment;
use App\Models\UserSkill;

class EmployerSearchController extends BaseApiController
{
    /**
     * Get jobs by request params.
    */
    public function getEmployerBySearch(Request $request)
    {
        try {
         $SerchReslt = DB::table('users')->where('role_id', '2')->get();
         return $this->sendResponse(
                $SerchReslt,
                'Employer list by params'
            );
    }catch(\Exception $e){
        return $this->sendError('Something went wrong', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
