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
use App\Http\Controllers\Api\EditAccomplishmentsController;
use App\Http\Controllers\Api\EditDesiredJobsController;
use App\Http\Controllers\Api\AccountSettingsController;

use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\CmsArticleController;

use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\ReportBugController;

use App\Http\Controllers\Api\JobSearchController;

use App\Http\Controllers\Api\SocialAuthController;

Route::post('/login', [AuthController::class, 'login']);
// LinkedIn routes
Route::post('/auth/linkedin/redirect', [SocialAuthController::class, 'redirectToLinkedIn']);
Route::post('/auth/linkedin/callback', [SocialAuthController::class, 'handleLinkedInCallback']);

// Google routes
Route::post('/auth/google/redirect', [SocialAuthController::class, 'redirectToGoogle']);
Route::post('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// The controller automatically detects which flow it is based on the state parameter
/* Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
Route::get('/auth/linkedin/callback', [SocialAuthController::class, 'handleLinkedInCallback']);

Route::post('/forgot-password', [ForgotpasswordController::class, 'forgotPassword']);
Route::post('/forgot-password/otp-verification', [ForgotpasswordController::class, 'otpVerification']);
Route::post('/forgot-password/reset-password', [ForgotpasswordController::class, 'resetPassword']); */

//Registration with complete profile
Route::post('/signup/cv', [RegistrationController::class, 'test_cv_parse']);

Route::post('/signup', [RegistrationController::class, 'registration']);
Route::post('/signup/resend-otp', [RegistrationController::class, 'resendOtp']);
Route::post('/signup/verification-top', [RegistrationController::class, 'registerVerification']);

Route::post('/forgot-password', [ForgotpasswordController::class, 'forgotPassword']);
Route::post('/forgot-password/otp-verification', [ForgotpasswordController::class, 'otpVerification']);
Route::post('/forgot-password/reset-password', [ForgotpasswordController::class, 'resetPassword']);

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
Route::get('/get-course/{qualification_id}', [CommonController::class, 'get_course']);
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
Route::get('/get-course-type', [CommonController::class, 'get_course_type']);
Route::get('/get-report-bug-category', [CommonController::class, 'get_report_bug_category']);
Route::get('/get-interested-in', [CommonController::class, 'get_interestedIn']);
Route::get('/get-it-skill', [CommonController::class, 'get_itSkill']);
Route::get('/get-city-by-param', [CommonController::class, 'get_city_by_param']);
Route::get('/get-homepage', [CommonController::class, 'get_homepage']);
Route::get('/get-general-settings', [CommonController::class, 'get_general_settings']);
Route::get('/get-testimonials', [CommonController::class, 'get_testimonials']);
Route::get('/get-testimonials/{slug}', [CommonController::class, 'get_testimonials_details']);
Route::get('/get-designation-by-param', [CommonController::class, 'get_designation_by_param']);
Route::get('/get-industry-by-param', [CommonController::class, 'get_industry_by_param']);

Route::get('/get-jobsearch-keys', [CommonController::class, 'get_jobsearch_keys']);

Route::get('/get-faq-category/{slug}', [FaqController::class, 'getFaqCategory']);
Route::get('/get-faq-by-category', [FaqController::class, 'getFaqByCategory']);

Route::get('/get-page', [CmsArticleController::class, 'getPage']);
Route::get('/get-article', [CmsArticleController::class, 'getArticle']);

Route::post('/contact-us', [ContactUsController::class, 'postContactUs']);
Route::post('/report-bug', [ReportBugController::class, 'postReportBug']);

Route::post('/get-jobs/{job_type}', [JobSearchController::class, 'getJobsByParams']);
Route::post('/get-jobs/{job_type}/{id}', [JobSearchController::class, 'getJobDetails']);
Route::get('/get-saved-jobs', [JobSearchController::class, 'getSavedJobs']);


Route::group([
    'middleware' => ['auth:api'],
], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    /**
        * same endpoint for jobseekers and Employers
    */
    Route::get('/profile', [AuthController::class, 'getUser']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/signup/setup-profile/{user}', [RegistrationController::class, 'setupProfile']);
    Route::post('/signup/complete-profile/{user}', [RegistrationController::class, 'completeProfile']);

    Route::post('/update-profile', [EditProfileController::class, 'updateProfileData']);
    Route::delete('/delete-profile-picture', [EditProfileController::class, 'removeProfilePicture']);

    Route::get('/resume-headline', [EditProfessionalDetailsController::class, 'getResumeHeadline']);
    Route::post('/resume-headline', [EditProfessionalDetailsController::class, 'postResumeHeadline']);
    Route::get('/keyskills', [EditProfessionalDetailsController::class, 'getKeyskills']);
    Route::post('/keyskills', [EditProfessionalDetailsController::class, 'postKeyskills']);
    Route::delete('/keyskills/{id}', [EditProfessionalDetailsController::class, 'deleteKeyskill']);
    Route::get('/itskills', [EditProfessionalDetailsController::class, 'getItskillsList']);
    Route::get('/itskills/{id}', [EditProfessionalDetailsController::class, 'getItskillDetails']);
    Route::post('/itskills', [EditProfessionalDetailsController::class, 'postItskills']);
    Route::delete('/itskills/{id}', [EditProfessionalDetailsController::class, 'deleteItkill']);
    Route::post('/update-itskills/{id}', [EditProfessionalDetailsController::class, 'updateItskills']);
    Route::get('/professional-details', [EditProfessionalDetailsController::class, 'getProfessionalDetails']);
    Route::post('/professional-details', [EditProfessionalDetailsController::class, 'postProfessionalDetails']);
    Route::get('/profile-summery', [EditProfessionalDetailsController::class, 'getProfileSummery']);
    Route::post('/profile-summery', [EditProfessionalDetailsController::class, 'postProfileSummery']);
    Route::get('/profile-completed-percentages', [EditProfessionalDetailsController::class, 'getProfileCompletedPercentages']);

    Route::get('/get-personal-details', [EditPersonalDetailsController::class, 'getPersonalDetails']);
    Route::post('/post-personal-details', [EditPersonalDetailsController::class, 'postPersonalDetails']);

    Route::get('/get-educational-details', [EditEducationalDetailsController::class, 'getEducationalDetails']);
    Route::post('/update-educational-details/{id}', [EditEducationalDetailsController::class, 'updateEducationalDetails']);
    Route::post('/post-educational-details', [EditEducationalDetailsController::class, 'postEducationalDetails']);

    Route::get('/get-employment-list', [EditEmploymentDetailsController::class, 'getEmploymentList']);
    Route::get('/get-employment-list/{id}', [EditEmploymentDetailsController::class, 'getEmploymentDetails']);
    Route::post('/update-employment-details/{id}', [EditEmploymentDetailsController::class, 'updateEmploymentDetails']);
    Route::post('/post-employment-details', [EditEmploymentDetailsController::class, 'postEmploymentDetails']);

    Route::post('/delete-cv', [EditResumeController::class, 'deleteResume']);
    Route::post('/post-cv', [EditResumeController::class, 'postResume']);

    Route::get('/get-certifications', [EditAccomplishmentsController::class, 'getCertificationDetails']);
    Route::post('/update-certifications/{id}', [EditAccomplishmentsController::class, 'updateCertificationDetails']);
    Route::post('/post-certifications', [EditAccomplishmentsController::class, 'postCertificationDetails']);
    Route::get('/get-online-profile', [EditAccomplishmentsController::class, 'getOnlineProfile']);
    Route::post('/post-online-profile', [EditAccomplishmentsController::class, 'postOnlineProfile']);
    Route::get('/get-work-sample', [EditAccomplishmentsController::class, 'getWorkSampleDetails']);
    Route::post('/update-work-sample/{id}', [EditAccomplishmentsController::class, 'updateWorkSampleDetails']);
    Route::post('/post-work-sample', [EditAccomplishmentsController::class, 'postWorkSampleDetails']);

    Route::get('/get-desired-jobs', [EditDesiredJobsController::class, 'getDesiredJobs']);
    Route::post('/post-desired-jobs', [EditDesiredJobsController::class, 'postDesiredJobs']);

    Route::get('/get-account-settings', [AccountSettingsController::class, 'getAccountSettingsDetails']);
    Route::post('/post-actively-looking-for', [AccountSettingsController::class, 'postActivelyLookingFor']);
    Route::post('/post-account-settings', [AccountSettingsController::class, 'postAccountSettings']);
    Route::post('/post-hide-my-profile', [AccountSettingsController::class, 'postHideMyProfile']);
    Route::post('/send-verification-otp', [AccountSettingsController::class, 'sendVerificationOtp']);
    Route::post('/verification-otp', [AccountSettingsController::class, 'verificationOtp']);
    Route::post('/change-password', [AccountSettingsController::class, 'changePassword']);

    Route::post('/post-job-apply', [JobSearchController::class, 'postJobApply']);
    Route::get('/get-jobseeker-jobs', [JobSearchController::class, 'jobseekerAppliedJobs']);
    Route::post('/shortlisted-jobs', [JobSearchController::class, 'shortlistedJob']);
    Route::get('/get-shortlisted-jobs', [JobSearchController::class, 'getShortlistedJob']);
    Route::get('/get-matched-jobs', [JobSearchController::class, 'getMatchedJobsForJobseeker']);

    // Google Account Linking
    Route::post('/auth/google/connect', [SocialAuthController::class, 'initiateGoogleConnect']);
    Route::post('/auth/google/link', [SocialAuthController::class, 'linkGoogleAccount']);
    Route::post('/auth/google/disconnect', [SocialAuthController::class, 'disconnectGoogleAccount']);

    // LinkedIn Account Linking
    Route::post('/auth/linkedin/connect', [SocialAuthController::class, 'initiateLinkedInConnect']);
    Route::post('/auth/linkedin/link', [SocialAuthController::class, 'linkLinkedInAccount']);
    Route::post('/auth/linkedin/disconnect', [SocialAuthController::class, 'disconnectLinkedInAccount']);

    // Test configuration (optional - for debugging)
    Route::get('/auth/test-config', [SocialAuthController::class, 'testConfig']);

});

/**
 * Starts employer auth section
*/
use App\Http\Controllers\Api\Employer\EmployerRegistrationController;
use App\Http\Controllers\Api\Employer\EmployerAuthController;
use App\Http\Controllers\Api\Employer\EditEmployerProfileController;
use App\Http\Controllers\Api\Employer\EditEmployerBusinessInfoController;
// use App\Http\Controllers\Api\Employer\ForgotpasswordController as EmployerForgotpasswordController;
use App\Http\Controllers\Api\Employer\EmployerJobseekerController;
use App\Http\Controllers\Api\Employer\EmployerFolderController;
use App\Http\Controllers\Api\Employer\EmployerUserController;
use App\Http\Controllers\Api\Employer\EmployerBrandsController;

Route::post('/employer/signup', [EmployerRegistrationController::class, 'registration']);
Route::post('/employer/signup/resend-otp', [EmployerRegistrationController::class, 'resendOtp']);
Route::post('/employer/signup/verification-top', [EmployerRegistrationController::class, 'registerVerification']);

Route::post('/employer/forgot-password', [ForgotpasswordController::class, 'forgotPassword']);
Route::post('/employer/forgot-password/otp-verification', [ForgotpasswordController::class, 'otpVerification']);
Route::post('/employer/forgot-password/reset-password', [ForgotpasswordController::class, 'resetPassword']);

Route::post('/employer/login', [EmployerAuthController::class, 'login']);

Route::group([
    'middleware' => ['auth:api'],
    'prefix' => 'employer',
], function () {
    Route::post('/logout', [EmployerAuthController::class, 'logout']);
    // Route::get('/profile', [EmployerAuthController::class, 'getUser']);
    Route::post('/change-password', [EmployerAuthController::class, 'changePassword']);
    Route::post('/signup/setup-company-profile/{user}', [EmployerRegistrationController::class, 'setupCompanyProfile']);

    Route::post('/update-profile', [EditEmployerProfileController::class, 'updateProfileData']);
    Route::post('/delete-profile-picture', [EditEmployerProfileController::class, 'removeProfilePicture']);

    Route::post('/update-business-profile', [EditEmployerBusinessInfoController::class, 'updateBusinessData']);

    /* Route::post('/send-verification-otp', [AccountSettingsController::class, 'sendVerificationOtp']);
    Route::post('/verification-otp', [AccountSettingsController::class, 'verificationOtp']);
    Route::post('/change-password', [AccountSettingsController::class, 'changePassword']); */

    Route::get('/get-blocked-jobseeker', [EmployerJobseekerController::class, 'getBlockedByJobseeker']);

    Route::post('/cv-folder/save-profile', [EmployerFolderController::class, 'saveProfile']);
    Route::post('/cv-folder/status/{id}', [EmployerFolderController::class, 'changeStatus']);
    Route::resource('/cv-folder', EmployerFolderController::class);

    Route::post('/user/delete/{id}', [EmployerUserController::class, 'destroy']);
    Route::post('/user/status/{id}', [EmployerUserController::class, 'changeStatus']);
    Route::resource('/user', EmployerUserController::class);

    Route::post('/brands/delete/{id}', [EmployerBrandsController::class, 'destroy']);
    Route::post('/brands/status/{id}', [EmployerBrandsController::class, 'changeStatus']);
    Route::resource('/brands', EmployerBrandsController::class);

});

Route::prefix('cv')->group(function () {
    Route::post('/parse-deepseek', [ResumeParserController::class, 'parse']);
    Route::post('/parse', [CVParserController::class, 'parse']);
    Route::get('/formats', [CVParserController::class, 'supportedFormats']);
});
/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */
