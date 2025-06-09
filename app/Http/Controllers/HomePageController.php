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
                                $section10Image1 = env('UPLOADS_URL') . $upload_folder . '/' . $section10_image1;
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
                                $section10Image2 = env('UPLOADS_URL') . $upload_folder . '/' . $section10_image2;
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
                                $section10Image3 = env('UPLOADS_URL') . $upload_folder . '/' . $section10_image3;
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

                    $fields = [
                        'section1_title'                    => strip_tags($postData['section1_title']),
                        'section1_description'              => strip_tags($postData['section1_description']),
                        'section1_button_text'              => strip_tags($postData['section1_button_text']),
                        'section2_title'                    => strip_tags($postData['section2_title']),
                        'section2_description'              => strip_tags($postData['section2_description']),
                        'section2_button_text'              => strip_tags($postData['section2_button_text']),
                        'section4_title'                    => strip_tags($postData['section4_title']),
                        'section4_country'                  => ((!empty($section4_country))?json_encode($section4_country):''),
                        'section4_city'                     => ((!empty($section4_city))?json_encode($section4_city):''),
                        'section5_title'                    => strip_tags($postData['section5_title']),
                        'section6_title'                    => strip_tags($postData['section6_title']),
                        'section6_description'              => strip_tags($postData['section6_description']),
                        'section6_button_text'              => strip_tags($postData['section6_button_text']),
                        'section7_title'                    => strip_tags($postData['section7_title']),
                        'section7_description'              => strip_tags($postData['section7_description']),
                        'section8_title'                    => strip_tags($postData['section8_title']),
                        'section8_description'              => strip_tags($postData['section8_description']),
                        'section9_title'                    => strip_tags($postData['section9_title']),
                        'section9_description'              => strip_tags($postData['section9_description']),
                        'section10_title'                   => strip_tags($postData['section10_title']),
                        'section10_description'             => strip_tags($postData['section10_description']),
                        'section10_image1'                  => $section10Image1,
                        'section10_image2'                  => $section10Image2,
                        'section10_image3'                  => $section10Image3,
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
