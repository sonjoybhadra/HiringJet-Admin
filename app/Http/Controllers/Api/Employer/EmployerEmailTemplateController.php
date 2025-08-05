<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;

use App\Models\Country;
use App\Models\City;
use App\Models\Designation;
use App\Models\EmployerEmailtemplate;
use App\Models\User;

class EmployerEmailTemplateController extends BaseApiController
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
            'template_name' => 'required|string|max:255',
            'from_email_user_id' => 'required|integer',
            'designation' => 'required',
            'experience_max' => 'required|integer',
            'experience_min' => 'required|integer',
            'country' => 'required',
            'city' => 'required',
            'currency_id' => 'required|integer',
            'salary_max' => 'required|integer',
            'salary_min' => 'required|integer',
            'message' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $has_duplicate = EmployerEmailtemplate::where('user_id', auth()->user()->id)
                                            ->where('template_name', 'ilike', '%'.$request->template_name.'%')
                                            ->where('from_email_user_id', $request->from_email_user_id)
                                            ->get()->count();
            if($has_duplicate > 0){
                return $this->sendError('Duplicate Error', 'Duplicate found.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $country = new Country();
            $country_id = $country->getCountryId($request->country);
            $city = new City();
            $city_id = $city->getCityId($request->city, $country_id);

            $designation = new Designation();
            $designation_id = $designation->getDesignationId($request->designation);
            EmployerEmailtemplate::insert([
                'user_id'=> auth()->user()->id,
                'template_name'=> $request->template_name,
                'from_email_user_id'=> $request->from_email_user_id,
                'designation_id'=> $designation_id,
                'experience_max'=> $request->experience_max,
                'experience_min'=> $request->experience_min,
                'country_id'=> $country_id,
                'city_id' => $city_id,
                'currency_id' => $request->currency_id,
                'salary_max' => $request->salary_max,
                'salary_min' => $request->salary_min,
                'message'=> $request->message,
                'owner_id'=> auth()->user()->id,
                'status'=> 1
            ]);

            return $this->sendResponse($this->getList(), 'Email template added successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
    */
    public function show(string $id)
    {
        $data = EmployerEmailtemplate::where('id', $id)->first();
        return $this->sendResponse($data, 'Details of Email template');
    }

    /**
     * Registered member step 1.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'template_name' => 'required|string|max:255',
            'from_email_user_id' => 'required|integer',
            'designation' => 'required',
            'experience_max' => 'required|integer',
            'experience_min' => 'required|integer',
            'country' => 'required',
            'city' => 'required',
            'currency_id' => 'required|integer',
            'salary_max' => 'required|integer',
            'salary_min' => 'required|integer',
            'message' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $has_duplicate = EmployerEmailtemplate::where('user_id', auth()->user()->id)
                                            ->where('template_name', 'ilike', '%'.$request->template_name.'%')
                                            ->where('from_email_user_id', $request->from_email_user_id)
                                            ->where('id', '!=', $id)
                                            ->get()->count();
            if($has_duplicate > 0){
                return $this->sendError('Duplicate Error', 'Duplicate found.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $country = new Country();
            $country_id = $country->getCountryId($request->country);
            $city = new City();
            $city_id = $city->getCityId($request->city, $country_id);

            $designation = new Designation();
            $designation_id = $designation->getDesignationId($request->designation);
            EmployerEmailtemplate::find($id)->update([
                'template_name'=> $request->template_name,
                'from_email_user_id'=> $request->from_email_user_id,
                'designation_id'=> $designation_id,
                'experience_max'=> $request->experience_max,
                'experience_min'=> $request->experience_min,
                'country_id'=> $country_id,
                'city_id' => $city_id,
                'currency_id' => $request->currency_id,
                'salary_max' => $request->salary_max,
                'salary_min' => $request->salary_min,
                'message'=> $request->message,
                'status'=> 1
            ]);

            return $this->sendResponse($this->getList(), 'Email template updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = EmployerEmailtemplate::findOrFail($id);
        $data->delete();

        return $this->sendResponse($this->getList(), 'Email template deleted successfully.');
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

        $data = EmployerEmailtemplate::findOrFail($id);
        $data->status = $request->status;
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();

        return $this->sendResponse($this->getList(), 'Email template status updated successfully.');
    }

    private function getList(){
        $own_list = EmployerEmailtemplate::with('from_email_user')
                                ->with('designations')
                                ->with('countries')
                                ->with('cities')
                                ->with('currency')
                                ->where('user_id', auth()->user()->id)
                                ->latest()
                                ->get();
        $shared_list = EmployerEmailtemplate::with('from_email_user')
                                ->with('designations')
                                ->with('countries')
                                ->with('cities')
                                ->with('currency')
                                ->where('user_id', auth()->user()->id)
                                ->where('owner_id', '!=', auth()->user()->id)
                                ->latest()
                                ->get();

        return [
            'own_list' => $own_list,
            'shared_list'  => $shared_list
        ];
    }

    public function share(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'emplyer_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $template = EmployerEmailtemplate::find($id);
            $has_data = EmployerEmailtemplate:: where('user_id', $request->emplyer_id)
                                        ->where('template_name', strtolower($template->template_name))
                                        ->count();
            if($has_data > 0){
                return $this->sendError('Error', 'Same Email template is already exists.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            EmployerEmailtemplate::insert([
                'user_id'=> auth()->user()->id,
                'template_name'=> $template->template_name,
                'from_email_user_id'=> $template->from_email_user_id,
                'designation_id'=> $template->designation_id,
                'experience_max'=> $template->experience_max,
                'experience_min'=> $template->experience_min,
                'country_id'=> $template->country_id,
                'city_id' => $template->city_id,
                'currency_id' => $template->currency_id,
                'salary_max' => $template->salary_max,
                'salary_min' => $template->salary_min,
                'message'=> $template->message,
                'owner_id'=> $template->owner_id,
                'status'=> 1
            ]);

            return $this->sendResponse($this->getList(), 'Email template shared with selected user successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
