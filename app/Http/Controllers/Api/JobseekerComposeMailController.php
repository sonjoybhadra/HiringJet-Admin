<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use App\Models\EmployerComposeMail;
use App\Models\EmployerComposeMailRecepients;
use App\Models\EmployerComposeMailReply;

class JobseekerComposeMailController extends BaseApiController
{
    public function __construct()
    {
        // Optional middleware: $this->middleware('auth:api');
    }

    /**
     *  Jobseeker Inbox List
     */
    public function inbox()
    {
        try {
            $jobseekerId = auth()->id();

            $inbox = EmployerComposeMailRecepients::where('jobseeker_id', $jobseekerId)
                ->with([
                    'composeEmail' => function ($query) {
                        $query->select('id', 'user_id', 'from_email', 'subject', 'message', 'created_at')
                              ->with(['user:id,first_name,last_name,email,country_code,phone']);
                    }
                ])
                ->orderBy('id', 'desc')
                ->paginate(10);

            return $this->sendResponse($inbox, 'Jobseeker inbox list fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error fetching inbox', $e->getMessage());
        }
    }

    /**
     *  View a Specific Mail
     */
    public function viewMail($id)
    {
        try {
            $jobseekerId = auth()->id();

            $mail = EmployerComposeMailRecepients::where('id', $id)
                ->where('jobseeker_id', $jobseekerId)
                ->with([
                    'composeEmail' => function ($query) {
                        $query->with(['user:id,first_name,last_name,email,country_code,phone']);
                    },
                    'composeEmail.replies' => function ($query) use ($jobseekerId) {
                        $query->where('jobseeker_id', $jobseekerId)
                              ->orWhereNull('jobseeker_id')
                              ->orderBy('id', 'asc');
                    }
                ])
                ->firstOrFail();

            // Mark as viewed
            $mail->update(['view_status' => true]);

            return $this->sendResponse($mail, 'Mail details fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error fetching mail', $e->getMessage());
        }
    }

    /**
     *  Jobseeker Reply to Employer
     */
    public function reply(Request $request, $compose_email_id)
    {
        $validator = Validator::make($request->all(), [
            'reply_message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $jobseekerId = auth()->id();

            $reply = EmployerComposeMailReply::create([
                'compose_email_id' => $compose_email_id,
                'jobseeker_id' => $jobseekerId,
                'reply_message' => $request->reply_message,
            ]);

            // Update reply status
            EmployerComposeMailRecepients::where('compose_email_id', $compose_email_id)
                ->where('jobseeker_id', $jobseekerId)
                ->update(['reply_status' => true]);

            return $this->sendResponse($reply, 'Reply sent successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error sending reply', $e->getMessage());
        }
    }
}
