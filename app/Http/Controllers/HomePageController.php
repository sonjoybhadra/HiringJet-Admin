<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\HomePage;
use App\Models\Country;
use App\Models\City;
use App\Models\UserActivity;
use App\Services\SiteAuthService;
use App\Helpers\Helper;
use Auth;
use Session;
use Hash;
use DB;

class HomePageController extends Controller
{
    protected $siteAuthService;
    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
        $this->data = array(
            'title'             => 'Home Page',
            'controller'        => 'HomePageController',
            'controller_route'  => 'home-page',
            'primary_key'       => 'id',
            'table_name'        => 'home_pages',
        );
    }
    /* manage */
        public function manage(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'home-page.add-edit';
            $data['row']                    = HomePage::where('id', '=', 1)->first();
            $data['countries']              = Country::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['cities']                 = City::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->limit(1000)->get();
            if($request->isMethod('post')){
                $postData = $request->all();
                // Helper::pr($request->file('section10_image1'),0);
                // Helper::pr($request->file('section10_image2'),0);
                // Helper::pr($request->file('section10_image3'),0);
                $rules = [
                    'section1_title'           => 'required',
                    'section2_title'           => 'required',
                    'section4_title'           => 'required',
                    'section5_title'           => 'required',
                    'section6_title'           => 'required',
                    'section7_title'           => 'required',
                    'section8_title'           => 'required',
                    'section9_title'           => 'required',
                    'section10_title'           => 'required',
                ];
                if($this->validate($request, $rules)){
                    /* section10_image1 */
                        $upload_folder = 'home-page';
                        $imageFile      = $request->file('section10_image1');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('section10_image1', $imageName, $upload_folder, 'image');
                            if($uploadedFile['status']){
                                $section10_image1 = $uploadedFile['newFilename'];
                                $section10Image1 = env('UPLOADS_URL_PATH') . $upload_folder . '/' . $section10_image1;
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $section10_image1 = $data['row']->section10_image1;
                            $section10Image1 = $section10_image1;
                        }
                    /* section10_image1 */
                    /* section10_image2 */
                        $upload_folder = 'home-page';
                        $imageFile      = $request->file('section10_image2');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('section10_image2', $imageName, $upload_folder, 'image');
                            if($uploadedFile['status']){
                                $section10_image2 = $uploadedFile['newFilename'];
                                $section10Image2 = env('UPLOADS_URL_PATH') . $upload_folder . '/' . $section10_image2;
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $section10_image2 = $data['row']->section10_image2;
                            $section10Image2 = $section10_image2;
                        }
                    /* section10_image2 */
                    /* section10_image3 */
                        $upload_folder = 'home-page';
                        $imageFile      = $request->file('section10_image3');
                        if($imageFile != ''){
                            $imageName      = $imageFile->getClientOriginalName();
                            $uploadedFile   = $this->upload_single_file('section10_image3', $imageName, $upload_folder, 'image');
                            if($uploadedFile['status']){
                                $section10_image3 = $uploadedFile['newFilename'];
                                $section10Image3 = env('UPLOADS_URL_PATH') . $upload_folder . '/' . $section10_image3;
                            } else {
                                return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                            }
                        } else {
                            $section10_image3 = $data['row']->section10_image3;
                            $section10Image3 = $section10_image3;
                        }
                    /* section10_image3 */

                    $section4_country = [];
                    if(array_key_exists("section4_country",$postData)){
                        $section4_country = $postData['section4_country'];
                    }
                    $section4_city = [];
                    if(array_key_exists("section4_city",$postData)){
                        $section4_city = $postData['section4_city'];
                    }

                    /* Section 3 images */
                        $section3_box_text = array_values(array_filter($postData['section3_box_text'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));
                        $section3_box_number = array_values(array_filter($postData['section3_box_number'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));

                        $image_array            = $request->file('section3_box_image');
                        if(!empty($image_array)){
                            $uploadedFile       = $this->siteAuthService->commonFileArrayUpload('home-page', $image_array, 'image');
                            if(!empty($uploadedFile)){
                                $images    = $uploadedFile;
                            } else {
                                $images    = [];
                            }
                        }
                        $image_link3 = [];
                        if(!empty($images)){
                            for($i=0;$i<count($images);$i++){
                                $image_link3[] = env('UPLOADS_URL_PATH').'home-page/'.$images[$i];
                            }
                        } else {
                            $image_link3 = (($data['row'])?json_decode($data['row']->section5_box_image):[]);
                        }
                    /* Section 3 images */
                    /* Section 5 images */
                        $section5_box_name = array_values(array_filter($postData['section5_box_name'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));

                        $image_array            = $request->file('section5_box_image');
                        if(!empty($image_array)){
                            $uploadedFile       = $this->siteAuthService->commonFileArrayUpload('home-page', $image_array, 'image');
                            if(!empty($uploadedFile)){
                                $images    = $uploadedFile;
                            } else {
                                $images    = [];
                            }
                        }
                        $image_link5 = [];
                        if(!empty($images)){
                            for($i=0;$i<count($images);$i++){
                                $image_link5[] = env('UPLOADS_URL_PATH').'home-page/'.$images[$i];
                            }
                        } else {
                            $image_link5 = (($data['row'])?json_decode($data['row']->section5_box_image):[]);
                        }
                    /* Section 5 images */
                    /* Section 7 images */
                        $section7_box_name = array_values(array_filter($postData['section7_box_name'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));
                        $section7_box_link_name = array_values(array_filter($postData['section7_box_link_name'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));
                        $section7_box_link_url = array_values(array_filter($postData['section7_box_link_url'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));
                        $section7_box_description = array_values(array_filter($postData['section7_box_description'], function($value) {
                            return !(is_null($value) || $value === '');
                        }));

                        $image_array            = $request->file('section7_box_image');
                        if(!empty($image_array)){
                            $uploadedFile       = $this->siteAuthService->commonFileArrayUpload('home-page', $image_array, 'image');
                            if(!empty($uploadedFile)){
                                $images    = $uploadedFile;
                            } else {
                                $images    = [];
                            }
                        }
                        $image_link7 = [];
                        if(!empty($images)){
                            for($i=0;$i<count($images);$i++){
                                $image_link7[] = env('UPLOADS_URL_PATH').'home-page/'.$images[$i];
                            }
                        } else {
                            $image_link7 = (($data['row'])?json_decode($data['row']->section7_box_image):[]);
                        }
                    /* Section 7 images */

                    $section1 = [];
                    $section2 = [];
                    $section3 = [];
                    $section4 = [];
                    $section5 = [];
                    $section6 = [];
                    $section7 = [];
                    $section8 = [];
                    $section9 = [];
                    $section10 = [];

                    $section1 = [
                        'title'         => strip_tags($postData['section1_title']),
                        'description'   => strip_tags($postData['section1_description']),
                        'button_text'   => strip_tags($postData['section1_button_text']),
                    ];
                    $section2 = [
                        'title'         => strip_tags($postData['section2_title']),
                        'description'   => strip_tags($postData['section2_description']),
                        'button_text'   => strip_tags($postData['section2_button_text']),
                    ];
                    $section3 = [
                        'box_text'          => ((!empty($section3_box_text))?json_encode($section3_box_text):''),
                        'box_number'        => ((!empty($section3_box_number))?json_encode($section3_box_number):''),
                        'box_image'         => ((!empty($image_link3))?json_encode($image_link3):''),
                    ];
                    $section4 = [
                        'title'         => strip_tags($postData['section4_title']),
                        'country'       => ((!empty($section4_country))?json_encode($section4_country):''),
                        'city'          => ((!empty($section4_city))?json_encode($section4_city):''),
                    ];
                    $section5 = [
                        'title'                 => strip_tags($postData['section5_title']),
                        'box_name'              => ((!empty($section5_box_name))?json_encode($section5_box_name):''),
                        'box_image'             => ((!empty($image_link5))?json_encode($image_link5):''),
                    ];
                    $section6 = [
                        'title'                 => strip_tags($postData['section6_title']),
                        'description'           => strip_tags($postData['section6_description']),
                        'button_text'           => strip_tags($postData['section6_button_text']),
                    ];
                    $section7 = [
                        'title'                 => strip_tags($postData['section7_title']),
                        'description'           => strip_tags($postData['section7_description']),
                        'box_name'              => ((!empty($section7_box_name))?json_encode($section7_box_name):''),
                        'box_link_name'         => ((!empty($section7_box_link_name))?json_encode($section7_box_link_name):''),
                        'box_link_url'          => ((!empty($section7_box_link_url))?json_encode($section7_box_link_url):''),
                        'box_description'       => ((!empty($section7_box_description))?json_encode($section7_box_description):''),
                        'box_image'             => ((!empty($image_link7))?json_encode($image_link7):''),
                    ];
                    $section8 = [
                        'title'                    => strip_tags($postData['section8_title']),
                        'description'              => strip_tags($postData['section8_description']),
                    ];
                    $section9 = [
                        'title'                    => strip_tags($postData['section9_title']),
                        'description'              => strip_tags($postData['section9_description']),
                    ];
                    $section10 = [
                        'title'                   => strip_tags($postData['section10_title']),
                        'description'             => strip_tags($postData['section10_description']),
                        'image1'                  => $section10Image1,
                        'image2'                  => $section10Image2,
                        'image3'                  => $section10Image3,
                    ];

                    $fields = [
                        'section1_title'                    => strip_tags($postData['section1_title']),
                        'section1_description'              => strip_tags($postData['section1_description']),
                        'section1_button_text'              => strip_tags($postData['section1_button_text']),
                        'section2_title'                    => strip_tags($postData['section2_title']),
                        'section2_description'              => strip_tags($postData['section2_description']),
                        'section2_button_text'              => strip_tags($postData['section2_button_text']),
                        'section3_box_text'                 => ((!empty($section3_box_text))?json_encode($section3_box_text):''),
                        'section3_box_number'               => ((!empty($section3_box_number))?json_encode($section3_box_number):''),
                        'section3_box_image'                => ((!empty($image_link3))?json_encode($image_link3):''),
                        'section4_title'                    => strip_tags($postData['section4_title']),
                        'section4_country'                  => ((!empty($section4_country))?json_encode($section4_country):''),
                        'section4_city'                     => ((!empty($section4_city))?json_encode($section4_city):''),
                        'section5_title'                    => strip_tags($postData['section5_title']),
                        'section5_box_name'                 => ((!empty($section5_box_name))?json_encode($section5_box_name):''),
                        'section5_box_image'                => ((!empty($image_link5))?json_encode($image_link5):''),
                        'section6_title'                    => strip_tags($postData['section6_title']),
                        'section6_description'              => strip_tags($postData['section6_description']),
                        'section6_button_text'              => strip_tags($postData['section6_button_text']),
                        'section7_title'                    => strip_tags($postData['section7_title']),
                        'section7_description'              => strip_tags($postData['section7_description']),
                        'section7_box_name'                 => ((!empty($section7_box_name))?json_encode($section7_box_name):''),
                        'section7_box_link_name'            => ((!empty($section7_box_link_name))?json_encode($section7_box_link_name):''),
                        'section7_box_link_url'             => ((!empty($section7_box_link_url))?json_encode($section7_box_link_url):''),
                        'section7_box_description'          => ((!empty($section7_box_description))?json_encode($section7_box_description):''),
                        'section7_box_image'                => ((!empty($image_link7))?json_encode($image_link7):''),
                        'section8_title'                    => strip_tags($postData['section8_title']),
                        'section8_description'              => strip_tags($postData['section8_description']),
                        'section9_title'                    => strip_tags($postData['section9_title']),
                        'section9_description'              => strip_tags($postData['section9_description']),
                        'section10_title'                   => strip_tags($postData['section10_title']),
                        'section10_description'             => strip_tags($postData['section10_description']),
                        'section10_image1'                  => $section10Image1,
                        'section10_image2'                  => $section10Image2,
                        'section10_image3'                  => $section10Image3,
                        'section1'                          => json_encode($section1),
                        'section2'                          => json_encode($section2),
                        'section3'                          => json_encode($section3),
                        'section4'                          => json_encode($section4),
                        'section5'                          => json_encode($section5),
                        'section6'                          => json_encode($section6),
                        'section7'                          => json_encode($section7),
                        'section8'                          => json_encode($section8),
                        'section9'                          => json_encode($section9),
                        'section10'                         => json_encode($section10),
                        'status'                            => 1,
                    ];
                    // Helper::pr($fields);
                    HomePage::where($this->data['primary_key'], '=', 1)->update($fields);
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
