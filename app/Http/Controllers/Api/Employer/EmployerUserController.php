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
        $sql = User::where('users.role_id', $this->employer)
                    ->where('users.parent_id', auth()->user()->id);
                    // ->with('user_subjects');
        if(!empty($request->search)){
            $sql->where('first_name', 'like', '%'.$request->search.'%');
            $sql->orWhere('last_name', 'like', '%'.$request->search.'%');
            $sql->orWhere('email', 'like', '%'.$request->search.'%');
            $sql->orWhere('email', 'like', '%'.$request->search.'%');
        }
        $list = $sql->latest()->get();
        if($list->count() > 0){
            foreach($list as $index => $val){
                $list[$index]->user_subjects = UserSubject::select('subjects.id', 'subjects.name')
                                                            ->join('subjects', 'subjects.id', '=', 'user_subjects.subject_id')
                                                            ->where('user_subjects.user_id', $val->id)
                                                            ->get()
                                                            ->toArray();
            }
        }
        return $this->sendResponse([
            'list'=> $list,
            'subscription_details'=> auth()->user()->user_subscriptions[0] ?? []
        ], 'Member User list.');
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
            'email' => 'required|email|max:150|unique:users,email,' . $request->id,
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
                // 'parent_id' => auth()->user()->id,
                'role_id'=> $this->employer,
                'first_name'=> $request->first_name,
                'last_name'=> $request->last_name,
                'email'=> $request->email,
                'password'=> Hash::make($pwd),
                // 'profile_image'     => $image_path,
                'status'=> 1
            ]);

            if($user_id){
                UserEmployer::create([
                    'user_id'=> $user_id,
                    'country'=> "0",
                    'first_name'=> $request->first_name,
                    'last_name'=> $request->last_name,
                    'email'=> $request->email,
                    'city_id'=> NULL,
                    'emarati'=> NULL,
                    'business_license'=> NULL,
                    'tax_registration_number'=> NULL,
                    'company_type' => NULL,
                    'employer_identification_no' => NULL
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
        $list = User::where('id', $id)->first();
        $list->user_subjects = UserSubject::select('subjects.id', 'subjects.name')
                                            ->join('subjects', 'subjects.id', '=', 'user_subjects.subject_id')
                                            ->where('user_subjects.user_id', $list->id)
                                            ->get()
                                            ->toArray();
        return $this->sendResponse($list, 'SME details.');
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
            'email' => 'required|email|max:150|unique:users,email,' . $id,
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'integer|exists:subjects,id',
            'status' => 'required|boolean',  // Assuming status is a boolean (1 or 0)
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try{
            $data = User::findOrFail($id);
            $data->name = $request->first_name.' '.$request->last_name;
            $data->email = $request->email;
            $data->status = $request->status;
            if(!empty($request->password)){
                $data->password = Hash::make($request->password);
            }
            if (request()->hasFile('image')) {
                $file = request()->file('image');
                $fileName = md5($file->getClientOriginalName() .'_'. time()) . "." . $file->getClientOriginalExtension();
                Storage::disk('public')->put('uploads/user/'.$fileName, file_get_contents($file));
                $data->profile_image = 'storage/uploads/user/'.$fileName;
            }
            $data->save();

            $userDetails = UserDetails::where('user_id', $id)->first();
            $userDetails->first_name = $request->first_name;
            $userDetails->last_name = $request->last_name;
            $userDetails->email = $request->email;
            $userDetails->country_code = $request->country_code;
            $userDetails->phone = $request->phone;
            $userDetails->save();
            if(!empty($request->subjects)){
                UserSubject::where([
                                    'user_id'=> $id
                                ])->delete();
                foreach($request->subjects as $subject){
                    if(!empty($subject)){
                        UserSubject::create([
                            'user_id'=> $id,
                            'subject_id'=> $subject
                        ]);
                    }
                }
            }

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
            $data = User::findOrFail($id);
            $data->status = 3;
            $data->delete();

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
    public function change_status(Request $request, $id)
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
