<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Models\User;
use App\Models\UserEmployer;

use App\Mail\RegistrationSuccess;
/**-------------------------- SME -------------------------------- */
class EmployerUserController extends BaseApiController
{
    private $employer;

    public function __construct()
    {
        $this->employer = env('EMPLOYER_ROLE_ID');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $list = User::where('parent_id', auth()->user()->id)
                    ->with('user_employer_details')
                    ->latest()->get();
        return $this->sendResponse($list, 'User List.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * First Name, Last Name, Email ID, Contact Number, Role/Designation, Manage Permission and Usage limits: CV Search / Job posting.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users',
            'country_code' => 'required|max:5',
            'phone' => 'required|max:15|unique:users',
            'designation_id' => 'required|integer',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $my_str = "543ZAbcd062abXRLcd123PTas@t9876GTDX#EChFIH8130BnWqY";
            $my_str = str_shuffle($my_str);
            $pwd = substr($my_str, 0, 8);

            $user_id = User::insertGetId([
                'parent_id' => auth()->user()->id,
                'role_id'=> $this->employer,
                'first_name'=> $request->first_name,
                'last_name'=> $request->last_name,
                'email'=> $request->email,
                'country_code' => $request->country_code,
                'phone'=> $request->phone,
                'password'=> Hash::make($pwd),
                'status'=> 1,
                'emp_reg_type'=> 1
            ]);

            if($user_id){
                UserEmployer::insert([
                    'user_id'=> $user_id,
                    'first_name'=> $request->first_name,
                    'last_name'=> $request->last_name,
                    'email'=> $request->email,
                    'country_code'=> $request->country_code,
                    'phone' => $request->phone,
                    'designation_id'=> $request->designation_id,
                    'completed_steps'=> 2
                ]);

                $full_name = $request->first_name.' '.$request->last_name;
                $message = 'Your account registration has successfully completed. Now you can login using your registered email & password';
                Mail::to($request->email)->send(new RegistrationSuccess($request->email, $full_name, $message, $pwd));

                return $this->sendResponse([], 'Member account registration has successfully completed.');
            }else{
                return $this->sendError('Error', 'Sorry!! Unable to register user.');
            }
        }catch(\Exception $cus_ex){
            // Error through. Some error occurred
            return $this->sendError('Registration Error', $cus_ex->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $list = User::where('id', $id)
                    ->where('parent_id', auth()->user()->id)
                    ->with('user_employer_details')
                    ->first();
        return $this->sendResponse($list, 'Member details.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,'.$id,
            'country_code' => 'required|max:5',
            'phone' => 'required|max:15|unique:users,phone,'.$id,
            'designation_id' => 'required|integer',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $data = User::findOrFail($id);
            $data->first_name = $request->first_name;
            $data->last_name = $request->last_name;
            $data->email = $request->email;
            $data->country_code = $request->country_code;
            $data->phone = $request->phone;
            /* if (request()->hasFile('image')) {
                $file = request()->file('image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/'.$fileName, file_get_contents($file));
                $data->profile_image = 'storage/uploads/user/'.$fileName;
            } */
            $data->save();

            UserEmployer::where('user_id', $id)->update([
                    'first_name'=> $request->first_name,
                    'last_name'=> $request->last_name,
                    'email'=> $request->email,
                    'country_code'=> $request->country_code,
                    'phone' => $request->phone,
                    'designation_id'=> $request->designation_id,
                    'completed_steps'=> 2
                ]);

            return $this->sendResponse([], 'Member data has successfully updated.');
        }catch(\Exception $cus_ex){
            // Error through. Some error occurred
            return $this->sendError('Error', $cus_ex->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            User::where('id', $id)
                ->where('parent_id', auth()->user()->id)
                ->delete();

            return $this->sendResponse([], 'Member data has successfully deleted.');
        }catch(\Exception $cus_ex){
            return $this->sendError('Error', $cus_ex->getMessage(), 500);
        }
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

        $data = User::findOrFail($id);
        $data->status = $request->status;
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();

        return $this->sendResponse([], 'Member status has successfully changed.');
    }

}
