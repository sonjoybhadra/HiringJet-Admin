<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

use App\Models\Country;
use App\Models\City;
use App\Models\Designation;
use App\Models\EmployerComposeMail;
use App\Models\EmployerComposeMailRecepients;

class EmployerComposeMailController extends BaseApiController
{
    public function __construct()
    {
        //
    }

    /**
     * Display a listing of the resource.
    */
    public function index()
    {
        return $this->sendResponse($this->getList(), 'List of brands');
    }

    /**
     * Registered member step 1.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'template_name' => 'required|string|max:255',
            'from_email' => 'required|email',
            /* 'designation' => 'required',
            'experience_max' => 'required|integer',
            'experience_min' => 'required|integer',
            'country' => 'required',
            'city' => 'required',
            'currency_id' => 'required|integer',
            'salary_max' => 'required|integer',
            'salary_min' => 'required|integer', */
            'subject' => 'required|string',
            'message' => 'required|string',
            'recepients' => 'required|array',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            /* $has_duplicate = EmployerComposeMail::where('user_id', auth()->user()->id)
                                            ->where('template_name', 'ilike', '%'.$request->template_name.'%')
                                            ->where('from_email_user_id', $request->from_email_user_id)
                                            ->get()->count();
            if($has_duplicate > 0){
                return $this->sendError('Duplicate Error', 'Duplicate found.', Response::HTTP_UNPROCESSABLE_ENTITY);
            } */

            $country_id = $city_id = $designation_id = NULL;
            if(!empty($request->country)){
                $country = new Country();
                $country_id = $country->getCountryId($request->country);
            }
            if(!empty($request->city)){
                $city = new City();
                $city_id = $city->getCityId($request->city, $country_id);
            }
            if(!empty($request->designation)){
                $designation = new Designation();
                $designation_id = $designation->getDesignationId($request->designation);
            }
            $compose_email = EmployerComposeMail::create([
                'user_id'=> auth()->user()->id,
                // 'template_name'=> $request->template_name,
                'from_email'=> $request->from_email,
                'designation_id'=> $designation_id,
                'experience_max'=> $request->experience_max,
                'experience_min'=> $request->experience_min,
                'country_id'=> $country_id,
                'city_id' => $city_id,
                'currency_id' => $request->currency_id,
                'salary_max' => $request->salary_max,
                'salary_min' => $request->salary_min,
                'subject'=> $request->subject,
                'message'=> $request->message,
                'questions'=> !empty($request->questions) ? json_encode($request->questions) : NULL,
                'answers'=> !empty($request->answers) ? json_encode($request->answers) : NULL,
                'status'=> 1,
                'created_at'=> date('Y-m-d h:i:s')
            ]);

            if($compose_email){
                if(!empty($request->recepients)){
                    foreach($request->recepients as $recepient){
                        if(!empty($recepient)){
                            EmployerComposeMailRecepients::create([
                                'compose_email_id'=> $compose_email->id,
                                'jobseeker_id'=> $recepient,
                                'employer_id'=> auth()->user()->id,
                                'reply_status'=> 0,
                                'view_status'=>0,
                            ]);
                        }
                    }
                }
            }

            return $this->sendResponse($this->getList(), 'Compose Email added successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
    */
    public function show(string $id)
    {
        $data = EmployerComposeMail::where('id', $id)
                                ->with('recepients')
                                ->first();
        return $this->sendResponse($data, 'Details of Compose Email');
    }

    /**
     * Registered member step 1.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            // 'template_name' => 'required|string|max:255',
            'from_email' => 'required|email',
            /* 'designation' => 'required',
            'experience_max' => 'required|integer',
            'experience_min' => 'required|integer',
            'country' => 'required',
            'city' => 'required',
            'currency_id' => 'required|integer',
            'salary_max' => 'required|integer',
            'salary_min' => 'required|integer', */
            'subject' => 'required|string',
            'message' => 'required|string',
            'recepients' => 'required|array',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            /* $has_duplicate = EmployerEmailtemplate::where('user_id', auth()->user()->id)
                                            ->where('template_name', 'ilike', '%'.$request->template_name.'%')
                                            ->where('from_email_user_id', $request->from_email_user_id)
                                            ->where('id', '!=', $id)
                                            ->get()->count();
            if($has_duplicate > 0){
                return $this->sendError('Duplicate Error', 'Duplicate found.', Response::HTTP_UNPROCESSABLE_ENTITY);
            } */
            $country_id = $city_id = $designation_id = NULL;
            if(!empty($request->country)){
                $country = new Country();
                $country_id = $country->getCountryId($request->country);
            }
            if(!empty($request->city)){
                $city = new City();
                $city_id = $city->getCityId($request->city, $country_id);
            }
            if(!empty($request->designation)){
                $designation = new Designation();
                $designation_id = $designation->getDesignationId($request->designation);
            }
            EmployerComposeMail::find($id)->update([
                'from_email'=> $request->from_email,
                // 'from_email_user_id'=> $request->from_email_user_id,
                'designation_id'=> $designation_id,
                'experience_max'=> $request->experience_max,
                'experience_min'=> $request->experience_min,
                'country_id'=> $country_id,
                'city_id' => $city_id,
                'currency_id' => $request->currency_id,
                'salary_max' => $request->salary_max,
                'salary_min' => $request->salary_min,
                'subject'=> $request->subject,
                'message'=> $request->message,
                'questions'=> !empty($request->questions) ? json_encode($request->questions) : NULL,
                'answers'=> !empty($request->answers) ? json_encode($request->answers) : NULL,
            ]);
            if(!empty($request->recepients)){
                    EmployerComposeMailRecepients::where('compose_email_id', $id)->where('employer_id', auth()->user()->id)->delete();
                    foreach($request->recepients as $recepient){
                        if(!empty($recepient)){
                            EmployerComposeMailRecepients::create([
                                'compose_email_id'=> $id,
                                'jobseeker_id'=> $recepient,
                                'employer_id'=> auth()->user()->id,
                                'reply_status'=> 0,
                                'view_status'=>0,
                            ]);
                        }
                    }
                }

            return $this->sendResponse($this->getList(), 'Compose Email updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = EmployerComposeMail::findOrFail($id);
        $data->delete();

        return $this->sendResponse($this->getList(), 'Compose Email deleted successfully.');
    }

    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
    */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|int'
        ]);

        $data = EmployerComposeMail::findOrFail($id);
        $data->status = $request->status;
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();

        return $this->sendResponse($this->getList(), 'Compose Email status updated successfully.');
    }

    private function getList(){
        return EmployerComposeMail::with('recepients')->latest()->get();
    }

}
