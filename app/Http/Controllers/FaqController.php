<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\FaqCategory;
use App\Models\Faq;
use App\Helpers\Helper;
use Auth;
use Session;
use Hash;

class FaqController extends Controller
{
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'FAQ',
            'controller'        => 'FaqController',
            'controller_route'  => 'faq',
            'primary_key'       => 'id',
        );
    }
    /* list */
        public function list(){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'faq.list';
            $data['rows']                   = Faq::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']           = $this->data;
            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'faq_category_id'    => 'required',
                    'question'           => 'required',
                    'answer'             => 'required',
                ];
                if($this->validate($request, $rules)){
                    $fields = [
                        'faq_category_id'  => strip_tags($postData['faq_category_id']),
                        'question'         => strip_tags($postData['question']),
                        'answer'           => strip_tags($postData['answer']),
                    ];
                    Faq::insert($fields);
                    return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Inserted Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Add';
            $page_name                      = 'faq.add-edit';
            $data['row']                    = [];
            $data['cats']                   = FaqCategory::select('id', 'name')->where('status', '=', 1)->get();
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'faq.add-edit';
            $data['row']                    = Faq::where($this->data['primary_key'], '=', $id)->first();
            $data['cats']                   = FaqCategory::select('id', 'name')->where('status', '=', 1)->get();

            if($request->isMethod('post')){
                $postData = $request->all();
                $rules = [
                    'faq_category_id'    => 'required',
                    'question'           => 'required',
                    'answer'             => 'required',
                ];
                if($this->validate($request, $rules)){
                    $fields = [
                        'faq_category_id'  => strip_tags($postData['faq_category_id']),
                        'question'         => strip_tags($postData['question']),
                        'answer'           => strip_tags($postData['answer']),
                    ];
                    Faq::where($this->data['primary_key'], '=', $id)->update($fields);
                    return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Updated Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }
            echo $this->admin_after_login_layout($title,$page_name,$data);
        }
    /* edit */
    /* delete */
        public function delete(Request $request, $id){
            $id                             = Helper::decoded($id);
            $fields = [
                'status'             => 3,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            Faq::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' Deleted Successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = Faq::find($id);
            if ($model->status == 1)
            {
                $model->status  = 0;
                $msg            = 'Deactivated';
            } else {
                $model->status  = 1;
                $msg            = 'Activated';
            }            
            $model->save();
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' '.$msg.' Successfully !!!');
        }
    /* change status */
}
