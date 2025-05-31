<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;

use App\Models\UserResume;

class EditResumeController extends BaseApiController
{
    public function deleteResume(Request $request)
    {
        try{
            $has_data = UserResume::where('user_id', auth()->user()->id)->first();
            if($has_data){
                UserResume::find($has_data->id)->delete();
                if (file_exists($has_data->resume)) {
                    unlink($has_data->resume);
                }
            }

            return $this->sendResponse([], 'CV deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function postResume(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'resume' => 'nullable|mimes:pdf,doc,docx|max:2048', // max:2048 = 2MB
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            if (request()->hasFile('resume')) {
                $file = request()->file('resume');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/profile_resume'.$fileName, file_get_contents($file));
                $image_path = 'storage/uploads/user/profile_resume/'.$fileName;

                $has_data = UserResume::where('user_id', auth()->user()->id)->first();
                if($has_data){
                    if (file_exists($has_data->resume)) {
                        unlink($has_data->resume);
                    }
                    UserResume::where('id', $has_data->id)->update([
                        'resume' => $image_path,
                        'is_default' => 1
                    ]);
                }else{
                    UserResume::insert([
                        'user_id' => auth()->user()->id,
                        'resume' => $image_path,
                        'is_default' => 1
                    ]);
                    $this->calculate_profile_completed_percentage(auth()->user()->id, 'upload-cv'); //CV Uploads completes
                }

                return $this->sendResponse([], 'CV uploaded successfully.');
            }else{
                return $this->sendError('Error', 'Sorry!! Unable to upload your CV.');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
