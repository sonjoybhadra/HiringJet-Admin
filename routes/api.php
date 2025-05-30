<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgotpasswordController;

use App\Http\Controllers\Api\ResumeParserController;
use App\Http\Controllers\Api\CVParserController;

use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\RegistrationController;

use App\Http\Controllers\Api\EditProfileController;
use App\Http\Controllers\Api\EditProfessionalDetailsController;
use App\Http\Controllers\Api\EditPersonalDetailsController;
use App\Http\Controllers\Api\EditEducationalDetailsController;
use App\Http\Controllers\Api\EditEmploymentDetailsController;
use App\Http\Controllers\Api\EditResumeController;

Route::post('/login', [AuthController::class, 'login']);

//Login with Google & Linkdin
Route::post('/google-login', [AuthController::class, 'loginWithGoogle']);
Route::post('/linkedin-login', [AuthController::class, 'loginWithLinkedIn']);

Route::post('/forgot-password', [ForgotpasswordController::class, 'forgotPassword']);
Route::post('/forgot-password/otp-verification', [ForgotpasswordController::class, 'otpVerification']);
Route::post('/forgot-password/reset-password', [ForgotpasswordController::class, 'resetPassword']);

//Registration with complete profile
Route::post('/signup', [RegistrationController::class, 'registration']);
Route::post('/signup/resend-otp', [RegistrationController::class, 'resendOtp']);
Route::post('/signup/verification-top', [RegistrationController::class, 'registerVerification']);


/**
 * Common master data
*/
Route::get('/get-masters', [CommonController::class, 'get_masters_by_params']);
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
Route::get('/get-cast-category', [CommonController::class, 'get_cast_category']);
Route::get('/get-diverse-background', [CommonController::class, 'get_diverse_background']);
Route::get('/get-employment-type', [CommonController::class, 'get_employment_type']);


Route::group([
    'middleware' => ['auth:api'],
], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'getUser']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/signup/setup-profile/{user}', [RegistrationController::class, 'setupProfile']);
    Route::post('/signup/complete-profile/{user}', [RegistrationController::class, 'completeProfile']);

    Route::post('/update-profile', [EditProfileController::class, 'updateProfileData']);

    Route::get('/resume-headline', [EditProfessionalDetailsController::class, 'getResumeHeadline']);
    Route::post('/resume-headline', [EditProfessionalDetailsController::class, 'postResumeHeadline']);
    Route::get('/keyskills', [EditProfessionalDetailsController::class, 'getKeyskills']);
    Route::post('/keyskills', [EditProfessionalDetailsController::class, 'postKeyskills']);
    Route::get('/itskills', [EditProfessionalDetailsController::class, 'getItskills']);
    Route::post('/itskills', [EditProfessionalDetailsController::class, 'postItskills']);
    Route::get('/professional-details', [EditProfessionalDetailsController::class, 'getProfessionalDetails']);
    Route::post('/professional-details', [EditProfessionalDetailsController::class, 'postProfessionalDetails']);
    Route::get('/profile-summery', [EditProfessionalDetailsController::class, 'getProfileSummery']);
    Route::post('/profile-summery', [EditProfessionalDetailsController::class, 'postProfileSummery']);
    Route::get('/profile-completed-percentages', [EditProfessionalDetailsController::class, 'getProfileCompletedPercentages']);

    Route::get('/get-personal-details', [EditPersonalDetailsController::class, 'getPersonalDetails']);
    Route::post('/post-personal-details', [EditPersonalDetailsController::class, 'postPersonalDetails']);

    Route::get('/get-educational-details', [EditEducationalDetailsController::class, 'getEducationalDetails']);
    Route::post('/post-educational-details', [EditEducationalDetailsController::class, 'postEducationalDetails']);

    Route::delete('/delete-cv', [EditResumeController::class, 'deleteResume']);
    Route::post('/post-cv', [EditResumeController::class, 'postResume']);

});

Route::prefix('cv')->group(function () {
    Route::post('/parse-deepseek', [ResumeParserController::class, 'parse']);
    Route::post('/parse', [CVParserController::class, 'parse']);
    Route::get('/formats', [CVParserController::class, 'supportedFormats']);
});
/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */
