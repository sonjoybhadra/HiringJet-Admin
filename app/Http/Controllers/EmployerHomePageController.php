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

            $section1_db         = [];
            $section2_db         = [];
            $section3_db         = [];
            $section4_db         = [];
            $section5_db         = [];

            if($data['row']){
                $section1_db         = (($data['row']->section1 != '')?json_decode($data['row']->section1):[]);
                $section2_db         = (($data['row']->section2 != '')?json_decode($data['row']->section2):[]);
                $section3_db         = (($data['row']->section3 != '')?json_decode($data['row']->section3):[]);
                $section4_db         = (($data['row']->section4 != '')?json_decode($data['row']->section4):[]);
                $section5_db         = (($data['row']->section5 != '')?json_decode($data['row']->section5):[]);
            }
            
            if($request->isMethod('post')){
                $postData = $request->all();
                
                $rules = [
                    'section1_title'           => 'required',
                    'section2_title'           => 'required',
                    'section3_title'           => 'required',
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
                            $section5_image1 = ((!empty($section5_db))?$section5_db->image1:'');
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
                            $section5_image2 = ((!empty($section5_db))?$section5_db->image2:'');
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
                            $section5_image3 = ((!empty($section5_db))?$section5_db->image3:'');
                            $section5Image3 = $section5_image3;
                        }
                    /* section5_image3 */                    

                    /* Section 2 images */
                        $section2_box_text = array_values(array_filter($postData['section2_box_text'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));
                        $section2_box_link = array_values(array_filter($postData['section2_box_link'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));

                        $image_array            = $request->file('section2_box_image');
                        if(!empty($image_array)){
                            $uploadedFile       = $this->siteAuthService->commonFileArrayUpload('home-page', $image_array, 'image');
                            if(!empty($uploadedFile)){
                                $images    = $uploadedFile;
                            } else {
                                $images    = [];
                            }
                        }
                        $image_link2 = [];
                        if(!empty($images)){
                            for($i=0;$i<count($images);$i++){
                                $image_link2[] = '/uploads/'.'home-page/'.$images[$i];
                            }
                        } else {

                            $image_link2 = array_map(function($item) {
                                return $item->box_image;
                            }, $section2_db->box);
                        }
                    /* Section 2 images */
                    /* Section 3 images */
                        $section3_box_text = array_values(array_filter($postData['section3_box_text'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));
                        $section3_box_description = array_values(array_filter($postData['section3_box_description'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));
                        $section3_box_button1_text = array_values(array_filter($postData['section3_box_button1_text'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));
                        $section3_box_button1_link = array_values(array_filter($postData['section3_box_button1_link'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));
                        $section3_box_button2_text = array_values(array_filter($postData['section3_box_button2_text'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));
                        $section3_box_button2_link = array_values(array_filter($postData['section3_box_button2_link'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));

                        $image_array3            = $request->file('section3_box_image');
                        if(!empty($image_array3)){
                            $uploadedFile       = $this->siteAuthService->commonFileArrayUpload('home-page', $image_array3, 'image');
                            if(!empty($uploadedFile)){
                                $images3    = $uploadedFile;
                            } else {
                                $images3    = [];
                            }
                        }
                        $image_link3 = [];
                        if(!empty($images3)){
                            for($i=0;$i<count($images3);$i++){
                                $image_link3[] = '/uploads/'.'home-page/'.$images3[$i];
                            }
                        } else {

                            $image_link3 = array_map(function($item) {
                                return $item->box_image;
                            }, $section2_db->box);
                        }
                    /* Section 3 images */

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
                    
                    // section 2
                        $box2 = [];
                        if(!empty($section2_box_text)){
                            for($k=0;$k<count($section2_box_text);$k++){
                                $box2[] = [
                                    'box_text'      => $section2_box_text[$k],
                                    'box_link'      => $section2_box_link[$k],
                                    'box_image'     => $image_link2[$k],
                                ];
                            }
                        }
                        $section2 = [
                            'title'     => strip_tags($postData['section2_title']),
                            'box'       => $box2
                        ];
                    // section 2

                    // section 3
                        $box3 = [];
                        if(!empty($section3_box_text)){
                            for($k=0;$k<count($section3_box_text);$k++){
                                $box3[] = [
                                    'box_text'              => $section3_box_text[$k],
                                    'box_description'       => $section3_box_description[$k],
                                    'box_button1_text'      => $section3_box_button1_text[$k],
                                    'box_button1_link'      => $section3_box_button1_link[$k],
                                    'box_button2_text'      => $section3_box_button2_text[$k],
                                    'box_button2_link'      => $section3_box_button2_link[$k],
                                    'box_image'             => $image_link3[$k],
                                ];
                            }
                        }
                        $section2 = [
                            'title'     => strip_tags($postData['section3_title']),
                            'box'       => $box3
                        ];
                        Helper::pr($section2);
                    // section 3

                    $section4 = [
                        'title'         => strip_tags($postData['section4_title']),
                        'description'   => strip_tags($postData['section4_description']),
                        'button_text'   => strip_tags($postData['section4_button_text']),
                    ];                    
                    
                    $section5 = [
                        'title'                   => strip_tags($postData['section5_title']),
                        'description'             => strip_tags($postData['section5_description']),
                        'image1'                  => $section5Image1,
                        'image2'                  => $section5Image2,
                        'image3'                  => $section5Image3,
                    ];

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
