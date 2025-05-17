<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Common\TableController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\RoleController;
// use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\FaqCategoryController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\KeyskillController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\MostCommonEmailController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ReligionController;
use App\Http\Controllers\VisaStatusController;
use App\Http\Controllers\MaritalStatusController;
use App\Http\Controllers\ProfileCompleteController;
use App\Http\Controllers\NationalityController;
use App\Http\Controllers\CurrentWorkLevelController;
use App\Http\Controllers\QualificationController;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [UserController::class, 'login'])->name('login');
Route::post('signin', [UserController::class, 'login'])->name('signin');
Route::match(['get','post'],'/forgot-password', [UserController::class, 'forgotPassword']);
Route::match(['get','post'],'/validate-otp/{id}', [UserController::class, 'validateOtp']);
Route::match(['get','post'],'/resend-otp/{id}', [UserController::class, 'resendOtp']);
Route::match(['get','post'],'/reset-password/{id}', [UserController::class, 'resetPassword']);

Route::get('/table/fetch', [TableController::class, 'fetch']);
Route::get('/table/export', [TableController::class, 'export']);

Route::middleware(['auth'])->group(function () {
	Route::get('dashboard', [UserController::class, 'dashboard']);
	Route::get('logout', [UserController::class, 'logout']);
	Route::get('email-logs', [UserController::class, 'emailLogs']);
    Route::match(['get','post'],'/email-logs/details/{email}', [UserController::class, 'emailLogsDetails']);
    Route::get('login-logs', [UserController::class, 'loginLogs']);
    Route::get('user-activity-logs', [UserController::class, 'userActivityLogs']);
    Route::match(['get','post'], '/common-delete-image/{id1}/{id2}/{id3}/{id4}/{id5}', [UserController::class, 'commonDeleteImage']);
    /* setting */
        Route::get('settings', [UserController::class, 'settings']);
        Route::post('profile-settings', [UserController::class, 'profile_settings']);
        Route::post('general-settings', [UserController::class, 'general_settings']);
        Route::post('change-password', [UserController::class, 'change_password']);
        Route::post('email-settings', [UserController::class, 'email_settings']);
        Route::get('test-email', [UserController::class, 'testEmail']);
        Route::post('email-template', [UserController::class, 'email_template']);
        Route::post('sms-settings', [UserController::class, 'sms_settings']);
        Route::post('footer-settings', [UserController::class, 'footer_settings']);
        Route::post('seo-settings', [UserController::class, 'seo_settings']);
        Route::post('payment-settings', [UserController::class, 'payment_settings']);
    /* setting */
    /* access & permission */
        /* modules */
            Route::get('module/list', [ModuleController::class, 'list']);
            Route::match(['get', 'post'], 'module/add', [ModuleController::class, 'add']);
            Route::match(['get', 'post'], 'module/edit/{id}', [ModuleController::class, 'edit']);
            Route::get('module/delete/{id}', [ModuleController::class, 'delete']);
            Route::get('module/change-status/{id}', [ModuleController::class, 'change_status']);
        /* modules */
        /* roles */
            Route::get('role/list', [RoleController::class, 'list']);
            Route::match(['get', 'post'], 'role/add', [RoleController::class, 'add']);
            Route::match(['get', 'post'], 'role/edit/{id}', [RoleController::class, 'edit']);
            Route::get('role/delete/{id}', [RoleController::class, 'delete']);
            Route::get('role/change-status/{id}', [RoleController::class, 'change_status']);
        /* roles */
        /* admin users */
            // Route::get('admin-users/list', [AdminUserController::class, 'list']);
            // Route::match(['get', 'post'], 'admin-users/add', [AdminUserController::class, 'add']);
            // Route::match(['get', 'post'], 'admin-users/edit/{id}', [AdminUserController::class, 'edit']);
            // Route::get('admin-users/delete/{id}', [AdminUserController::class, 'delete']);
            // Route::get('admin-users/change-status/{id}', [AdminUserController::class, 'change_status']);
        /* admin users */
    /* access & permission */
    /* masters */
        /* industry */
            Route::get('industry/list', [IndustryController::class, 'list']);
            Route::match(['get', 'post'], 'industry/add', [IndustryController::class, 'add']);
            Route::match(['get', 'post'], 'industry/edit/{id}', [IndustryController::class, 'edit']);
            Route::get('industry/delete/{id}', [IndustryController::class, 'delete']);
            Route::get('industry/change-status/{id}', [IndustryController::class, 'change_status']);
        /* industry */
        /* designation */
            Route::get('designation/list', [DesignationController::class, 'list']);
            Route::match(['get', 'post'], 'designation/add', [DesignationController::class, 'add']);
            Route::match(['get', 'post'], 'designation/edit/{id}', [DesignationController::class, 'edit']);
            Route::get('designation/delete/{id}', [DesignationController::class, 'delete']);
            Route::get('designation/change-status/{id}', [DesignationController::class, 'change_status']);
        /* designation */
        /* keyskill */
            Route::get('keyskill/list', [KeyskillController::class, 'list']);
            Route::match(['get', 'post'], 'keyskill/add', [KeyskillController::class, 'add']);
            Route::match(['get', 'post'], 'keyskill/edit/{id}', [KeyskillController::class, 'edit']);
            Route::get('keyskill/delete/{id}', [KeyskillController::class, 'delete']);
            Route::get('keyskill/change-status/{id}', [KeyskillController::class, 'change_status']);
        /* keyskill */
        /* benefit */
            Route::get('benefit/list', [BenefitController::class, 'list']);
            Route::match(['get', 'post'], 'benefit/add', [BenefitController::class, 'add']);
            Route::match(['get', 'post'], 'benefit/edit/{id}', [BenefitController::class, 'edit']);
            Route::get('benefit/delete/{id}', [BenefitController::class, 'delete']);
            Route::get('benefit/change-status/{id}', [BenefitController::class, 'change_status']);
        /* benefit */
        /* availability */
            Route::get('availability/list', [AvailabilityController::class, 'list']);
            Route::match(['get', 'post'], 'availability/add', [AvailabilityController::class, 'add']);
            Route::match(['get', 'post'], 'availability/edit/{id}', [AvailabilityController::class, 'edit']);
            Route::get('availability/delete/{id}', [AvailabilityController::class, 'delete']);
            Route::get('availability/change-status/{id}', [AvailabilityController::class, 'change_status']);
        /* availability */
        /* university */
            Route::get('university/list', [UniversityController::class, 'list']);
            Route::match(['get', 'post'], 'university/add', [UniversityController::class, 'add']);
            Route::match(['get', 'post'], 'university/edit/{id}', [UniversityController::class, 'edit']);
            Route::get('university/delete/{id}', [UniversityController::class, 'delete']);
            Route::get('university/change-status/{id}', [UniversityController::class, 'change_status']);
        /* university */
        /* most-common-email */
            Route::get('most-common-email/list', [MostCommonEmailController::class, 'list']);
            Route::match(['get', 'post'], 'most-common-email/add', [MostCommonEmailController::class, 'add']);
            Route::match(['get', 'post'], 'most-common-email/edit/{id}', [MostCommonEmailController::class, 'edit']);
            Route::get('most-common-email/delete/{id}', [MostCommonEmailController::class, 'delete']);
            Route::get('most-common-email/change-status/{id}', [MostCommonEmailController::class, 'change_status']);
        /* most-common-email */
        /* language */
            Route::get('language/list', [LanguageController::class, 'list']);
            Route::match(['get', 'post'], 'language/add', [LanguageController::class, 'add']);
            Route::match(['get', 'post'], 'language/edit/{id}', [LanguageController::class, 'edit']);
            Route::get('language/delete/{id}', [LanguageController::class, 'delete']);
            Route::get('language/change-status/{id}', [LanguageController::class, 'change_status']);
        /* language */
        /* religion */
            Route::get('religion/list', [ReligionController::class, 'list']);
            Route::match(['get', 'post'], 'religion/add', [ReligionController::class, 'add']);
            Route::match(['get', 'post'], 'religion/edit/{id}', [ReligionController::class, 'edit']);
            Route::get('religion/delete/{id}', [ReligionController::class, 'delete']);
            Route::get('religion/change-status/{id}', [ReligionController::class, 'change_status']);
        /* religion */
        /* visa status */
            Route::get('visa-status/list', [VisaStatusController::class, 'list']);
            Route::match(['get', 'post'], 'visa-status/add', [VisaStatusController::class, 'add']);
            Route::match(['get', 'post'], 'visa-status/edit/{id}', [VisaStatusController::class, 'edit']);
            Route::get('visa-status/delete/{id}', [VisaStatusController::class, 'delete']);
            Route::get('visa-status/change-status/{id}', [VisaStatusController::class, 'change_status']);
        /* visa status */
        /* marital status */
            Route::get('marital-status/list', [MaritalStatusController::class, 'list']);
            Route::match(['get', 'post'], 'marital-status/add', [MaritalStatusController::class, 'add']);
            Route::match(['get', 'post'], 'marital-status/edit/{id}', [MaritalStatusController::class, 'edit']);
            Route::get('marital-status/delete/{id}', [MaritalStatusController::class, 'delete']);
            Route::get('marital-status/change-status/{id}', [MaritalStatusController::class, 'change_status']);
        /* marital status */
        /* profile-complete */
            Route::get('profile-complete/list', [ProfileCompleteController::class, 'list']);
            Route::match(['get', 'post'], 'profile-complete/add', [ProfileCompleteController::class, 'add']);
            Route::match(['get', 'post'], 'profile-complete/edit/{id}', [ProfileCompleteController::class, 'edit']);
            Route::get('profile-complete/delete/{id}', [ProfileCompleteController::class, 'delete']);
            Route::get('profile-complete/change-status/{id}', [ProfileCompleteController::class, 'change_status']);
        /* profile-complete */
        /* nationality */
            Route::get('nationality/list', [NationalityController::class, 'list']);
            Route::match(['get', 'post'], 'nationality/add', [NationalityController::class, 'add']);
            Route::match(['get', 'post'], 'nationality/edit/{id}', [NationalityController::class, 'edit']);
            Route::get('nationality/delete/{id}', [NationalityController::class, 'delete']);
            Route::get('nationality/change-status/{id}', [NationalityController::class, 'change_status']);
        /* nationality */
        /* current work level */
            Route::get('current-work-level/list', [CurrentWorkLevelController::class, 'list']);
            Route::match(['get', 'post'], 'current-work-level/add', [CurrentWorkLevelController::class, 'add']);
            Route::match(['get', 'post'], 'current-work-level/edit/{id}', [CurrentWorkLevelController::class, 'edit']);
            Route::get('current-work-level/delete/{id}', [CurrentWorkLevelController::class, 'delete']);
            Route::get('current-work-level/change-status/{id}', [CurrentWorkLevelController::class, 'change_status']);
        /* current work level */
        /* qualification */
            Route::get('qualification/list', [QualificationController::class, 'list']);
            Route::match(['get', 'post'], 'qualification/add', [QualificationController::class, 'add']);
            Route::match(['get', 'post'], 'qualification/edit/{id}', [QualificationController::class, 'edit']);
            Route::get('qualification/delete/{id}', [QualificationController::class, 'delete']);
            Route::get('qualification/change-status/{id}', [QualificationController::class, 'change_status']);
        /* qualification */
    /* masters */
    /* FAQs */
        /* faq category */
            Route::get('faq-category/list', [FaqCategoryController::class, 'list']);
            Route::match(['get', 'post'], 'faq-category/add', [FaqCategoryController::class, 'add']);
            Route::match(['get', 'post'], 'faq-category/edit/{id}', [FaqCategoryController::class, 'edit']);
            Route::get('faq-category/delete/{id}', [FaqCategoryController::class, 'delete']);
            Route::get('faq-category/change-status/{id}', [FaqCategoryController::class, 'change_status']);
        /* faq category */
        /* faq */
            Route::get('faq/list', [FaqController::class, 'list']);
            Route::match(['get', 'post'], 'faq/add', [FaqController::class, 'add']);
            Route::match(['get', 'post'], 'faq/edit/{id}', [FaqController::class, 'edit']);
            Route::get('faq/delete/{id}', [FaqController::class, 'delete']);
            Route::get('faq/change-status/{id}', [FaqController::class, 'change_status']);
        /* faq */
    /* FAQs */
    /* package */
        Route::get('package/list', [PackageController::class, 'list']);
        Route::match(['get', 'post'], 'package/add', [PackageController::class, 'add']);
        Route::match(['get', 'post'], 'package/edit/{id}', [PackageController::class, 'edit']);
        Route::get('package/delete/{id}', [PackageController::class, 'delete']);
        Route::get('package/change-status/{id}', [PackageController::class, 'change_status']);
    /* package */

});