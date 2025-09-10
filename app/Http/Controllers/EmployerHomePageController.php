<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\EmployerHomePage;
use App\Models\Country;
use App\Models\City;
use App\Models\UserActivity;
use App\Services\SiteAuthService;
use App\Helpers\Helper;
use Auth;
use Session;
use Hash;
use DB;

class EmployerHomePageController extends Controller
{
    protected $siteAuthService;
    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
        $this->data = array(
            'title'             => 'Employer Home Page',
            'controller'        => 'EmployerHomePageController',
            'controller_route'  => 'employer-home-page',
            'primary_key'       => 'id',
            'table_name'        => 'employer_home_pages',
        );
    }
    /* manage */
        public function manage(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'employer-home-page.add-edit';
            $data['row']                    = EmployerHomePage::where('id', '=', 1)->first();
            
            if($request->isMethod('post')){
                $postData = $request->all();
                
                $rules = [
                    'section1_title'           => 'required',
                    // 'section2_title'           => 'required',
                    // 'section3_title'           => 'required',
                    'section4_title'           => 'required',
                    'section5_title'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    /* section5_image1 */
                        $upload_folder = 'home-page';
                        $imageFile      = $request->file('section5_image1');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('section5_image1', $imageName, $upload_folder, 'image');
                            if($uploadedFile['status']){
                                $section5_image1 = $uploadedFile['newFilename'];
                                $section5Image1 = '/uploads/' . $upload_folder . '/' . $section5_image1;
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $section5_image1 = $data['row']->section5_image1;
                            $section5Image1 = $section5_image1;
                        }
                    /* section5_image1 */
                    /* section5_image2 */
                        $upload_folder = 'home-page';
                        $imageFile      = $request->file('section5_image2');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('section5_image2', $imageName, $upload_folder, 'image');
                            if($uploadedFile['status']){
                                $section5_image2 = $uploadedFile['newFilename'];
                                $section5Image2 = '/uploads/' . $upload_folder . '/' . $section5_image2;
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $section5_image2 = $data['row']->section5_image2;
                            $section5Image2 = $section5_image2;
                        }
                    /* section5_image2 */
                    /* section5_image3 */
                        $upload_folder = 'home-page';
                        $imageFile      = $request->file('section5_image3');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('section5_image3', $imageName, $upload_folder, 'image');
                            if($uploadedFile['status']){
                                $section5_image3 = $uploadedFile['newFilename'];
                                $section5Image3 = '/uploads/' . $upload_folder . '/' . $section5_image3;
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $section5_image3 = $data['row']->section5_image3;
                            $section5Image3 = $section5_image3;
                        }
                    /* section5_image3 */                    

                    /* Section 3 images */
                        // $section3_box_text = array_values(array_filter($postData['section3_box_text'], function($value) {
                        //     return !(is_null($value) || $value === '');
                        // }));
                        // $section3_box_number = array_values(array_filter($postData['section3_box_number'], function($value) {
                        //     return !(is_null($value) || $value === '');
                        // }));

                        // $image_array            = $request->file('section3_box_image');
                        // if(!empty($image_array)){
                        //     $uploadedFile       = $this->siteAuthService->commonFileArrayUpload('home-page', $image_array, 'image');
                        //     if(!empty($uploadedFile)){
                        //         $images    = $uploadedFile;
                        //     } else {
                        //         $images    = [];
                        //     }
                        // }
                        // $image_link3 = [];
                        // if(!empty($images)){
                        //     for($i=0;$i<count($images);$i++){
                        //         $image_link3[] = '/uploads/'.'home-page/'.$images[$i];
                        //     }
                        // } else {
                        //     $image_link3 = (($data['row'])?json_decode($data['row']->section5_box_image):[]);
                        // }
                    /* Section 3 images */
                    /* Section 5 images */
                        // $section5_box_name = array_values(array_filter($postData['section5_box_name'], function($value) {
                        //     return !(is_null($value) || $value === '');
                        // }));

                        // $image_array            = $request->file('section5_box_image');
                        // if(!empty($image_array)){
                        //     $uploadedFile       = $this->siteAuthService->commonFileArrayUpload('home-page', $image_array, 'image');
                        //     if(!empty($uploadedFile)){
                        //         $images    = $uploadedFile;
                        //     } else {
                        //         $images    = [];
                        //     }
                        // }
                        // $image_link5 = [];
                        // if(!empty($images)){
                        //     for($i=0;$i<count($images);$i++){
                        //         $image_link5[] = '/uploads/'.'home-page/'.$images[$i];
                        //     }
                        // } else {
                        //     $image_link5 = (($data['row'])?json_decode($data['row']->section5_box_image):[]);
                        // }
                    /* Section 5 images */
                    /* Section 7 images */
                        // $section7_box_name = array_values(array_filter($postData['section7_box_name'], function($value) {
                        //     return !(is_null($value) || $value === '');
                        // }));
                        // $section7_box_link_name = array_values(array_filter($postData['section7_box_link_name'], function($value) {
                        //     return !(is_null($value) || $value === '');
                        // }));
                        // $section7_box_link_url = array_values(array_filter($postData['section7_box_link_url'], function($value) {
                        //     return !(is_null($value) || $value === '');
                        // }));
                        // $section7_box_description = array_values(array_filter($postData['section7_box_description'], function($value) {
                        //     return !(is_null($value) || $value === '');
                        // }));

                        // $image_array            = $request->file('section7_box_image');
                        // if(!empty($image_array)){
                        //     $uploadedFile       = $this->siteAuthService->commonFileArrayUpload('home-page', $image_array, 'image');
                        //     if(!empty($uploadedFile)){
                        //         $images    = $uploadedFile;
                        //     } else {
                        //         $images    = [];
                        //     }
                        // }
                        // $image_link7 = [];
                        // if(!empty($images)){
                        //     for($i=0;$i<count($images);$i++){
                        //         $image_link7[] = '/uploads/'.'home-page/'.$images[$i];
                        //     }
                        // } else {
                        //     $image_link7 = (($data['row'])?json_decode($data['row']->section7_box_image):[]);
                        // }
                    /* Section 7 images */

                    $section1 = [];
                    $section2 = [];
                    $section3 = [];
                    $section4 = [];
                    $section5 = [];

                    $section1 = [
                        'title'         => strip_tags($postData['section1_title']),
                        'description'   => strip_tags($postData['section1_description']),
                        'button_text'   => strip_tags($postData['section1_button_text']),
                    ];
                    // $section2 = [
                    //     'title'         => strip_tags($postData['section2_title']),
                    //     'description'   => strip_tags($postData['section2_description']),
                    //     'button_text'   => strip_tags($postData['section2_button_text']),
                    // ];
                    // $section3 = [
                    //     'box_text'          => ((!empty($section3_box_text))?json_encode($section3_box_text):''),
                    //     'box_number'        => ((!empty($section3_box_number))?json_encode($section3_box_number):''),
                    //     'box_image'         => ((!empty($image_link3))?json_encode($image_link3):''),
                    // ];
                    // if(!empty($section3_box_text)){
                    //     for($k=0;$k<count($section3_box_text);$k++){
                    //         $section3[] = [
                    //             'box_text'      => $section3_box_text[$k],
                    //             'box_number'    => $section3_box_number[$k],
                    //             'box_image'     => $image_link3[$k],
                    //         ];
                    //     }
                    // }
                    $section4 = [
                        'title'         => strip_tags($postData['section4_title']),
                        'description'   => strip_tags($postData['section4_description']),
                        'button_text'   => strip_tags($postData['section4_button_text']),
                    ];
                    
                    // $section_data = [];
                    // if(!empty($section5_box_name)){
                    //     for($k=0;$k<count($section5_box_name);$k++){
                    //         $section_data[] = [
                    //             'box_text'      => $section5_box_name[$k],
                    //             'box_image'     => $image_link5[$k],
                    //         ];
                    //     }
                    // }
                    // $section5 = [
                    //     'title'                 => strip_tags($postData['section5_title']),
                    //     'section_data'          => $section_data,
                    // ];
                    // $section6 = [
                    //     'title'                 => strip_tags($postData['section6_title']),
                    //     'description'           => strip_tags($postData['section6_description']),
                    //     'button_text'           => strip_tags($postData['section6_button_text']),
                    // ];

                    // $section_data7 = [];
                    // if(!empty($section7_box_name)){
                    //     for($k=0;$k<count($section7_box_name);$k++){
                    //         $section_data7[] = [
                    //             'box_name'              => $section7_box_name[$k],
                    //             'box_link_name'         => $section7_box_link_name[$k],
                    //             'box_link_url'          => $section7_box_link_url[$k],
                    //             'box_description'       => $section7_box_description[$k],
                    //             'box_image'             => $image_link7[$k],
                    //         ];
                    //     }
                    // }
                    // $section7 = [
                    //     'title'                 => strip_tags($postData['section7_title']),
                    //     'description'           => strip_tags($postData['section7_description']),
                    //     'section_data'          => $section_data7,
                    // ];
                    // $section8 = [
                    //     'title'                    => strip_tags($postData['section8_title']),
                    //     'description'              => strip_tags($postData['section8_description']),
                    // ];
                    // $section9 = [
                    //     'title'                    => strip_tags($postData['section9_title']),
                    //     'description'              => strip_tags($postData['section9_description']),
                    // ];
                    $section5 = [
                        'title'                   => strip_tags($postData['section5_title']),
                        'description'             => strip_tags($postData['section5_description']),
                        'image1'                  => $section5Image1,
                        'image2'                  => $section5Image2,
                        'image3'                  => $section5Image3,
                    ];
                    Helper::pr($section5);

                    $fields = [
                        'section1'                          => json_encode($section1),
                        'section2'                          => json_encode($section2),
                        'section3'                          => json_encode($section3),
                        'section4'                          => json_encode($section4),
                        'section5'                          => json_encode($section5),
                        'status'                            => 1,
                    ];
                    // Helper::pr($fields);
                    EmployerHomePage::where($this->data['primary_key'], '=', 1)->update($fields);

                    /* user activity */
                        $activityData = [
                            'user_email'        => session('user_data')['email'],
                            'user_name'         => session('user_data')['name'],
                            'user_type'         => 'ADMIN',
                            'ip_address'        => $request->ip(),
                            'activity_type'     => 3,
                            'activity_details'  => $this->data['title'] . ' Updated',
                            'platform_type'     => 'WEB',
                        ];
                        UserActivity::insert($activityData);
                    /* user activity */
                    return redirect($this->data['controller_route'] . "/manage")->with('success_message', $this->data['title'].' Updated Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data                           = $this->siteAuthService ->admin_after_login_layout($title,$page_name,$data);
            return view('maincontents.' . $page_name, $data);
        }
    /* manage */
}
