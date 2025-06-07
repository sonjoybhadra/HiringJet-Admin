<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

use App\Models\UserAccountSetting;

class AccountSettingsController extends BaseApiController
{
    private function fetchAccountSettings($key = []){
        $sql = UserAccountSetting::where('user_id', auth()->user()->id);
        if(!empty($key)){
            $sql->whereIn('key', $key);
        }

        return $sql->get();
    }
    /**
     * Get account settings.
    */
    public function getAccountSettingsDetails(Request $request)
    {
        $sql = UserAccountSetting::where('user_id', auth()->user()->id);
        if($request->key){
            $sql->where('key', $request->key);
        }
        try {
            return $this->sendResponse(
                $this->fetchAccountSettings(),
                'User account settings details'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }
    /**
     * Post @params
    */
    public function postActivelyLookingFor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string',
            'options' => 'required_if:value,1|array',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $has_data = UserAccountSetting::where('user_id', auth()->user()->id)
                                    ->where('key', 'actively-looking-for')
                                    ->first();
            $post_data = [
                'user_id'=> auth()->user()->id,
                'key'=> 'actively-looking-for',
                'value'=> $request->value
            ];
            if($has_data){
                UserAccountSetting::where('id', $has_data->id)->update($post_data);
            }else{
                UserAccountSetting::create($post_data);
            }
            if($request->value == 1 && !empty($request->options)){
                $post_data = [
                    'user_id'=> auth()->user()->id,
                    'key'=> 'actively-looking-for-options',
                    'value'=> json_encode($request->options)
                ];

                $has_data = UserAccountSetting::where('user_id', auth()->user()->id)
                                        ->where('key', 'actively-looking-for-options')
                                        ->first();
                if($has_data){
                    UserAccountSetting::where('id', $has_data->id)->update($post_data);
                }else{
                    UserAccountSetting::create($post_data);
                }
            }
            return $this->sendResponse(
                $this->fetchAccountSettings(['actively-looking-for', 'actively-looking-for-options']),
                'Account settings updated successfuilly'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    /**
     * Post key list
     * account-deactive, recommended-job, career-news-&-update, promotion-offer, premium-service
    */

    public function postAccountSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'value' => 'required|string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $has_data = UserAccountSetting::where('user_id', auth()->user()->id)
                                    ->where('key', $request->key)
                                    ->first();
        $post_data = [
            'user_id'=> auth()->user()->id,
            'key'=> $request->key,
            'value'=> $request->value
        ];
        if($has_data){
            UserAccountSetting::where('id', $has_data->id)->update($post_data);
        }else{
            UserAccountSetting::create($post_data);
        }
        try {
            return $this->sendResponse(
                $this->fetchAccountSettings([$request->key]),
                'Settings updated successfuilly'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

    public function postHideMyProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|array'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $has_data = UserAccountSetting::where('user_id', auth()->user()->id)
                                    ->where('key', 'hide-my-profile')
                                    ->first();
            $post_data = [
                'user_id'=> auth()->user()->id,
                'key'=> 'hide-my-profile',
                'value'=> json_encode($request->value)
            ];
            if($has_data){
                UserAccountSetting::where('id', $has_data->id)->update($post_data);
            }else{
                UserAccountSetting::create($post_data);
            }
            return $this->sendResponse(
                $this->fetchAccountSettings(['hide-my-profile']),
                'Account settings updated successfuilly'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error', $e->getMessage());
        }
    }

}
