<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Illuminate\Support\Facades\Storage;

use App\Models\Country;
use App\Models\EmployerBrand;

class EmployerBrandsController extends BaseApiController
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
            'company_name' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'info' => 'required|string|max:255',
            'industry' => 'required|integer',
            'web_url' => 'required|url',
            'contact_person' => 'required|integer',
            'contact_person_designation' => 'required|integer',
            'address' => 'required',
            'country' => 'required',
            'zip_code' => 'required|numeric',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $has_duplicate = EmployerBrand::where('user_id', auth()->user()->id)
                                            ->where('company_name', 'ilike', '%'.$request->company_name.'%')
                                            ->where('contact_person_id', $request->contact_person)
                                            ->get()->count();
            if($has_duplicate > 0){
                return $this->sendError('Duplicate Error', 'Duplicate brand mapping is exists', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $logo = "";
            if (request()->hasFile('logo')) {
                $file = request()->file('logo');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/logo/'.$fileName, file_get_contents($file));
                $logo = 'public/storage/uploads/employer/logo/'.$fileName;
            }
            $country = new Country();
            $country_id = $country->getCountryId($request->country);
            EmployerBrand::insert([
                'user_id'=> auth()->user()->id,
                'company_name'=> $request->company_name,
                'company_logo'=> $logo,
                'info'=> $request->info,
                'industry_id'=> $request->industry,
                'contact_person_id'=> $request->contact_person,
                'contact_person_designation_id'=> $request->contact_person_designation,
                'web_url' => $request->web_url,
                'address' => $request->address,
                'country' => $country_id,
                'zip_code' => $request->zip_code,
                'status'=> 1
            ]);

            return $this->sendResponse($this->getList(), 'Brand added successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
    */
    public function show(string $id)
    {
        $data = EmployerBrand::where('id', $id)
                                ->with('industry')
                                ->with('contact_person')
                                ->with('contact_person_designation')
                                ->first();
        return $this->sendResponse($data, 'Details of brand');
    }

    /**
     * Registered member step 1.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            // 'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',// Max:5MB
            'info' => 'required|string|max:255',
            'industry' => 'required|integer',
            'web_url' => 'required|url',
            'contact_person' => 'required|integer',
            'contact_person_designation' => 'required|integer',
            'address' => 'required',
            'country' => 'required',
            'zip_code' => 'required|numeric',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $has_duplicate = EmployerBrand::where('user_id', auth()->user()->id)
                                            ->where('company_name', 'ilike', '%'.$request->company_name.'%')
                                            ->where('contact_person_id', $request->contact_person)
                                            ->where('id', '!=', $id)
                                            ->get()->count();
            if($has_duplicate > 0){
                return $this->sendError('Duplicate Error', 'Duplicate brand mapping is exists', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $country = new Country();
            $country_id = $country->getCountryId($request->country);
            $update_array = [
                'company_name'=> $request->company_name,
                'info'=> $request->info,
                'industry_id'=> $request->industry,
                'contact_person_id'=> $request->contact_person,
                'contact_person_designation_id'=> $request->contact_person_designation,
                'web_url' => $request->web_url,
                'address' => $request->address,
                'country' => $country_id,
                'zip_code' => $request->zip_code,
            ];
            if (request()->hasFile('logo')) {
                $file = request()->file('logo');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/employer/logo/'.$fileName, file_get_contents($file));
                $update_array['company_logo'] = 'public/storage/uploads/employer/logo/'.$fileName;
            }
            EmployerBrand::find($id)->update($update_array);

            return $this->sendResponse($this->getList(), 'Brand updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = EmployerBrand::findOrFail($id);
        $data->delete();

        return $this->sendResponse($this->getList(), 'Brand deleted successfully.');
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

        $data = EmployerBrand::findOrFail($id);
        $data->status = $request->status;
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();

        return $this->sendResponse($this->getList(), 'Brand status updated successfully.');
    }

    private function getList(){
        $list = EmployerBrand::with('industry')
                                ->with('contact_person')
                                ->with('contact_person_designation')
                                ->where('user_id', auth()->user()->id)
                                ->orderBy('company_name', 'ASC')
                                ->get();

        return $list;
    }

}
