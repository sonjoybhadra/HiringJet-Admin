<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgotpasswordController;

use App\Http\Controllers\Api\ResumeParserController;
use App\Http\Controllers\Api\CVParserController;

use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\RegistrationController;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [ForgotpasswordController::class, 'forgot_password']);
Route::post('/forgot-password/otp-verification', [ForgotpasswordController::class, 'otp_verification']);
Route::post('/forgot-password/reset-password', [ForgotpasswordController::class, 'reset_password']);

//Registration with complete profile
Route::post('/signup', [RegistrationController::class, 'registration']);
Route::post('/signup/resend-otp', [RegistrationController::class, 'resend_otp']);
Route::post('/signup/verification-top', [RegistrationController::class, 'register_verification']);
Route::post('/signup/setup-profile/{user}', [RegistrationController::class, 'setup_profile']);
Route::post('/signup/complete-profile/{user}', [RegistrationController::class, 'complete_profile']);

/**
 * Common master data
*/
Route::get('/get-jobcategory', [CommonController::class, 'get_jobcategory']);
Route::get('/get-industry', [CommonController::class, 'get_industry']);
Route::get('/get-designation', [CommonController::class, 'get_designation']);
Route::get('/get-employer', [CommonController::class, 'get_employer']);
Route::get('/get-country', [CommonController::class, 'get_country']);
Route::get('/get-city/{country_id}', [CommonController::class, 'get_city']);
Route::get('/get-country-code', [CommonController::class, 'get_country_code']);
Route::get('/get-currency', [CommonController::class, 'get_currency']);
Route::get('/get-keyskill', [CommonController::class, 'get_keyskill']);
Route::get('/get-perkbenefit', [CommonController::class, 'get_perkbenefit']);
Route::get('/get-availability', [CommonController::class, 'get_availability']);
Route::get('/get-currentworklevel', [CommonController::class, 'get_currentworklevel']);
Route::get('/get-functionalarea', [CommonController::class, 'get_functionalarea']);
Route::get('/get-onlineprofile', [CommonController::class, 'get_onlineprofile']);
Route::get('/get-qualification', [CommonController::class, 'get_qualification']);
Route::get('/get-course', [CommonController::class, 'get_course']);
Route::get('/get-specialization', [CommonController::class, 'get_specialization']);
Route::get('/get-nationality', [CommonController::class, 'get_nationality']);
Route::get('/get-religion', [CommonController::class, 'get_religion']);
Route::get('/get-university', [CommonController::class, 'get_university']);
Route::get('/get-mostcommonemail', [CommonController::class, 'get_mostcommonemail']);
Route::get('/get-language', [CommonController::class, 'get_language']);
Route::get('/get-maritalstatus', [CommonController::class, 'get_maritalstatus']);
Route::get('/get-proficiency-level', [CommonController::class, 'get_proficiency_level']);


Route::group([
    'middleware' => ['auth:api'],
], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'getUser']);
    Route::post('/change-password', [AuthController::class, 'change_password']);
    Route::post('/update-profile', [AuthController::class, 'update_profile']);

});



Route::prefix('cv')->group(function () {
    Route::post('/parse-deepseek', [ResumeParserController::class, 'parse']);
    Route::post('/parse', [CVParserController::class, 'parse']);
    Route::get('/formats', [CVParserController::class, 'supportedFormats']);
});
/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */
