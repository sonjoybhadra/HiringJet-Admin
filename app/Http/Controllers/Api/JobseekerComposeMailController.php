<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use App\Models\EmployerComposeMail;
use App\Models\EmployerComposeMailRecepients;
use App\Models\EmployerComposeMailReply;
use Illuminate\Support\Facades\Auth;

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
            $userId = Auth::id();

            $inbox = EmployerComposeMailRecepients::where('jobseeker_id', $userId)
                ->with([
                    'composeEmail' => function ($query) {
                        $query->with(['user:id,first_name,last_name,email']);
                    }
                ])
                ->orderByDesc('id')
                ->paginate(10);

            return $this->sendResponse($inbox, 'Inbox fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error fetching inbox', $e->getMessage());
        }
    }

    /**
     * View a specific mail and its replies
     */
    public function viewMail($id)
    {
        try {
            $userId = Auth::id();

            $mail = EmployerComposeMailRecepients::where('id', $id)
               ->with([
                        'composeEmail' => function ($query) {
                            $query->with(['user:id,first_name,last_name,email']);
                        },
                        'composeEmail.replies' => function ($query) use ($jobseekerId) {
                            $query->where('sender_id', $jobseekerId)
                                ->orWhere('receiver_id', $jobseekerId)
                                ->orderBy('id', 'asc');
                        },
                        'composeEmail.replies.sender:id,first_name,last_name,email',
                        'composeEmail.replies.receiver:id,first_name,last_name,email'
                    ])

                ->firstOrFail();

            // Optional: mark as viewed
            $mail->update(['view_status' => true]);

            return $this->sendResponse($mail, 'Mail details fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error fetching mail', $e->getMessage());
        }
    }

    /**
     * Send reply (employer or jobseeker)
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
            $senderId = Auth::id();

            // Get the main composed mail to determine receiver
            $composeEmail = EmployerComposeMail::findOrFail($compose_email_id);

            // Receiver: if sender is employer (composeEmail.user_id), receiver = jobseeker
            // For simplicity, assuming only 1 jobseeker per mail
            $recipient = EmployerComposeMailRecepients::where('compose_email_id', $compose_email_id)->firstOrFail();

            $receiverId = ($senderId == $composeEmail->user_id) ? $recipient->jobseeker_id : $composeEmail->user_id;

            $reply = EmployerComposeMailReply::create([
                'compose_email_id' => $compose_email_id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'reply_message' => $request->reply_message,
            ]);

            // Update reply_status
            $recipient->update(['reply_status' => true]);

            return $this->sendResponse($reply, 'Reply sent successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error sending reply', $e->getMessage());
        }
    }
}
