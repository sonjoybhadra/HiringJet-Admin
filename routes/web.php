<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Common\TableController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\FaqCategoryController;
use App\Http\Controllers\FaqSubCategoryController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\KeyskillController;
use App\Http\Controllers\ITskillController;
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
use App\Http\Controllers\OnlineProfileController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SEOPageController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\JobCategoryController;
use App\Http\Controllers\FunctionalAreaController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ContractTypeController;
use App\Http\Controllers\ExperienceLevelController;
use App\Http\Controllers\PostJobController;
use App\Http\Controllers\UploadPostJobController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\ReportBugController;
use App\Http\Controllers\JobseekerController;
use App\Http\Controllers\EmployerUserController;

// Route::get('/', function () {
//     return view('welcome');
// });

// GET route – to display the page
Route::get('/test-email-function', [AuthController::class, 'showEmailTestPage']);

// POST route – to send the email
Route::post('/test-email-function', [AuthController::class, 'testEmailFunction']);

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('signin', [AuthController::class, 'login'])->name('signin');
Route::match(['get','post'],'/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgotpassword');
Route::match(['get','post'],'/validate-otp/{id}', [AuthController::class, 'validateOtp'])->name('validateotp');
Route::match(['get','post'],'/resend-otp/{id}', [AuthController::class, 'resendOtp']);
Route::match(['get','post'],'/reset-password/{id}', [AuthController::class, 'resetPassword'])->name('resetpassword');
Route::match(['get','post'],'/page', [AuthController::class, 'page'])->name('page');

Route::get('/table/fetch', [TableController::class, 'fetch']);
Route::get('/table/export', [TableController::class, 'export']);

Route::middleware(['auth'])->group(function () {
	Route::get('dashboard', [AuthController::class, 'dashboard']);
	Route::get('logout', [AuthController::class, 'logout']);
	Route::get('email-logs', [AuthController::class, 'emailLogs']);
    Route::match(['get','post'],'/email-logs/details/{email}', [AuthController::class, 'emailLogsDetails']);
    Route::get('login-logs', [AuthController::class, 'loginLogs']);
    Route::get('user-activity-logs', [AuthController::class, 'userActivityLogs']);
    Route::match(['get','post'], '/common-delete-image/{id1}/{id2}/{id3}/{id4}/{id5}', [AuthController::class, 'commonDeleteImage']);
    /* setting */
        Route::get('settings', [AuthController::class, 'settings']);
        Route::post('profile-settings', [AuthController::class, 'profile_settings']);
        Route::post('general-settings', [AuthController::class, 'general_settings']);
        Route::post('change-password', [AuthController::class, 'change_password']);
        Route::post('email-settings', [AuthController::class, 'email_settings']);
        Route::get('test-email', [AuthController::class, 'testEmail']);
        Route::post('email-template', [AuthController::class, 'email_template']);
        Route::post('sms-settings', [AuthController::class, 'sms_settings']);
        Route::post('footer-settings', [AuthController::class, 'footer_settings']);
        Route::post('seo-settings', [AuthController::class, 'seo_settings']);
        Route::post('payment-settings', [AuthController::class, 'payment_settings']);
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
            Route::get('admin-user/list', [AdminUserController::class, 'list']);
            Route::match(['get', 'post'], 'admin-user/add', [AdminUserController::class, 'add']);
            Route::match(['get', 'post'], 'admin-user/edit/{id}', [AdminUserController::class, 'edit']);
            Route::get('admin-user/delete/{id}', [AdminUserController::class, 'delete']);
            Route::get('admin-user/change-status/{id}', [AdminUserController::class, 'change_status']);
        /* admin users */
    /* access & permission */
    /* masters */
        /* job category */
            Route::get('job-category/list', [JobCategoryController::class, 'list']);
            Route::match(['get', 'post'], 'job-category/add', [JobCategoryController::class, 'add']);
            Route::post('job-category/store', [JobCategoryController::class, 'store'])->name('job-category.store');
            Route::match(['get', 'post'], 'job-category/edit/{id}', [JobCategoryController::class, 'edit']);
            Route::get('job-category/delete/{id}', [JobCategoryController::class, 'delete']);
            Route::get('job-category/change-status/{id}', [JobCategoryController::class, 'change_status']);
        /* job category */
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
        /* employer */
            Route::get('employer/list', [EmployerController::class, 'list']);
            Route::match(['get', 'post'], 'employer/add', [EmployerController::class, 'add']);
            Route::match(['get', 'post'], 'employer/edit/{id}', [EmployerController::class, 'edit']);
            Route::get('employer/delete/{id}', [EmployerController::class, 'delete']);
            Route::get('employer/change-status/{id}', [EmployerController::class, 'change_status']);
            Route::get('/employer-suggestions', [EmployerController::class, 'suggest'])->name('employers.suggest');
        /* employer */
        /* country */
            Route::get('country/list', [CountryController::class, 'list']);
            Route::match(['get', 'post'], 'country/add', [CountryController::class, 'add']);
            Route::match(['get', 'post'], 'country/edit/{id}', [CountryController::class, 'edit']);
            Route::get('country/delete/{id}', [CountryController::class, 'delete']);
            Route::get('country/change-status/{id}', [CountryController::class, 'change_status']);
        /* country */
        /* city */
            Route::get('city/list', [CityController::class, 'list']);
            Route::match(['get', 'post'], 'city/add', [CityController::class, 'add']);
            Route::match(['get', 'post'], 'city/edit/{id}', [CityController::class, 'edit']);
            Route::get('city/delete/{id}', [CityController::class, 'delete']);
            Route::get('city/change-status/{id}', [CityController::class, 'change_status']);
        /* city */
        /* currency */
            Route::get('currency/list', [CurrencyController::class, 'list']);
            Route::match(['get', 'post'], 'currency/add', [CurrencyController::class, 'add']);
            Route::match(['get', 'post'], 'currency/edit/{id}', [CurrencyController::class, 'edit']);
            Route::get('currency/delete/{id}', [CurrencyController::class, 'delete']);
            Route::get('currency/change-status/{id}', [CurrencyController::class, 'change_status']);
        /* currency */
        /* keyskill */
            Route::get('keyskill/list', [KeyskillController::class, 'list']);
            Route::match(['get', 'post'], 'keyskill/add', [KeyskillController::class, 'add']);
            Route::match(['get', 'post'], 'keyskill/edit/{id}', [KeyskillController::class, 'edit']);
            Route::get('keyskill/delete/{id}', [KeyskillController::class, 'delete']);
            Route::get('keyskill/change-status/{id}', [KeyskillController::class, 'change_status']);
        /* keyskill */
        /* itskill */
            Route::get('itskill/list', [ITskillController::class, 'list']);
            Route::match(['get', 'post'], 'itskill/add', [ITskillController::class, 'add']);
            Route::match(['get', 'post'], 'itskill/edit/{id}', [ITskillController::class, 'edit']);
            Route::get('itskill/delete/{id}', [ITskillController::class, 'delete']);
            Route::get('itskill/change-status/{id}', [ITskillController::class, 'change_status']);
        /* itskill */
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
        /* functional area */
            Route::get('functional-area/list', [FunctionalAreaController::class, 'list']);
            Route::match(['get', 'post'], 'functional-area/add', [FunctionalAreaController::class, 'add']);
            Route::match(['get', 'post'], 'functional-area/edit/{id}', [FunctionalAreaController::class, 'edit']);
            Route::get('functional-area/delete/{id}', [FunctionalAreaController::class, 'delete']);
            Route::get('functional-area/change-status/{id}', [FunctionalAreaController::class, 'change_status']);
        /* functional area */
        /* online profile */
            Route::get('online-profile/list', [OnlineProfileController::class, 'list']);
            Route::match(['get', 'post'], 'online-profile/add', [OnlineProfileController::class, 'add']);
            Route::match(['get', 'post'], 'online-profile/edit/{id}', [OnlineProfileController::class, 'edit']);
            Route::get('online-profile/delete/{id}', [OnlineProfileController::class, 'delete']);
            Route::get('online-profile/change-status/{id}', [OnlineProfileController::class, 'change_status']);
        /* online profile */
        /* qualification */
            Route::get('qualification/list', [QualificationController::class, 'list']);
            Route::match(['get', 'post'], 'qualification/add', [QualificationController::class, 'add']);
            Route::match(['get', 'post'], 'qualification/edit/{id}', [QualificationController::class, 'edit']);
            Route::get('qualification/delete/{id}', [QualificationController::class, 'delete']);
            Route::get('qualification/change-status/{id}', [QualificationController::class, 'change_status']);
        /* qualification */
        /* course */
            Route::get('course/list', [CourseController::class, 'list']);
            Route::match(['get', 'post'], 'course/add', [CourseController::class, 'add']);
            Route::match(['get', 'post'], 'course/edit/{id}', [CourseController::class, 'edit']);
            Route::get('course/delete/{id}', [CourseController::class, 'delete']);
            Route::get('course/change-status/{id}', [CourseController::class, 'change_status']);
        /* course */
        /* specialization */
            Route::get('specialization/list', [SpecializationController::class, 'list']);
            Route::match(['get', 'post'], 'specialization/add', [SpecializationController::class, 'add']);
            Route::match(['get', 'post'], 'specialization/edit/{id}', [SpecializationController::class, 'edit']);
            Route::get('specialization/delete/{id}', [SpecializationController::class, 'delete']);
            Route::get('specialization/change-status/{id}', [SpecializationController::class, 'change_status']);
        /* specialization */
        /* department */
            Route::get('department/list', [DepartmentController::class, 'list']);
            Route::match(['get', 'post'], 'department/add', [DepartmentController::class, 'add']);
            Route::match(['get', 'post'], 'department/edit/{id}', [DepartmentController::class, 'edit']);
            Route::get('department/delete/{id}', [DepartmentController::class, 'delete']);
            Route::get('department/change-status/{id}', [DepartmentController::class, 'change_status']);
        /* department */
        /* contract type */
            Route::get('contract-type/list', [ContractTypeController::class, 'list']);
            Route::match(['get', 'post'], 'contract-type/add', [ContractTypeController::class, 'add']);
            Route::match(['get', 'post'], 'contract-type/edit/{id}', [ContractTypeController::class, 'edit']);
            Route::get('contract-type/delete/{id}', [ContractTypeController::class, 'delete']);
            Route::get('contract-type/change-status/{id}', [ContractTypeController::class, 'change_status']);
        /* contract type */
        /* experience level */
            Route::get('experience-level/list', [ExperienceLevelController::class, 'list']);
            Route::match(['get', 'post'], 'experience-level/add', [ExperienceLevelController::class, 'add']);
            Route::match(['get', 'post'], 'experience-level/edit/{id}', [ExperienceLevelController::class, 'edit']);
            Route::get('experience-level/delete/{id}', [ExperienceLevelController::class, 'delete']);
            Route::get('experience-level/change-status/{id}', [ExperienceLevelController::class, 'change_status']);
        /* experience level */
    /* masters */
    /* FAQs */
        /* faq category */
            Route::get('faq-category/list', [FaqCategoryController::class, 'list']);
            Route::match(['get', 'post'], 'faq-category/add', [FaqCategoryController::class, 'add']);
            Route::match(['get', 'post'], 'faq-category/edit/{id}', [FaqCategoryController::class, 'edit']);
            Route::get('faq-category/delete/{id}', [FaqCategoryController::class, 'delete']);
            Route::get('faq-category/change-status/{id}', [FaqCategoryController::class, 'change_status']);
        /* faq category */
        /* faq sub category */
            Route::get('faq-sub-category/list', [FaqSubCategoryController::class, 'list']);
            Route::match(['get', 'post'], 'faq-sub-category/add', [FaqSubCategoryController::class, 'add']);
            Route::match(['get', 'post'], 'faq-sub-category/edit/{id}', [FaqSubCategoryController::class, 'edit']);
            Route::get('faq-sub-category/delete/{id}', [FaqSubCategoryController::class, 'delete']);
            Route::get('faq-sub-category/change-status/{id}', [FaqSubCategoryController::class, 'change_status']);
        /* faq sub category */
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
    /* page */
        Route::get('page/list', [PageController::class, 'list']);
        Route::match(['get', 'post'], 'page/add', [PageController::class, 'add']);
        Route::match(['get', 'post'], 'page/edit/{id}', [PageController::class, 'edit']);
        Route::get('page/delete/{id}', [PageController::class, 'delete']);
        Route::get('page/change-status/{id}', [PageController::class, 'change_status']);
    /* page */
    /* page */
        Route::get('seo-page/list', [SEOPageController::class, 'list']);
        Route::match(['get', 'post'], 'seo-page/add', [SEOPageController::class, 'add']);
        Route::match(['get', 'post'], 'seo-page/edit/{id}', [SEOPageController::class, 'edit']);
        Route::get('seo-page/delete/{id}', [SEOPageController::class, 'delete']);
        Route::get('seo-page/change-status/{id}', [SEOPageController::class, 'change_status']);
    /* page */
    
    /* post job */
        Route::get('post-job/list', [PostJobController::class, 'list']);
        Route::get('job/pending-list', [PostJobController::class, 'pendingList']);
        Route::get('job/reject-list', [PostJobController::class, 'rejectList']);
        Route::match(['get', 'post'], 'post-job/add', [PostJobController::class, 'add']);
        Route::match(['get', 'post'], 'post-job/edit/{id}', [PostJobController::class, 'edit']);
        Route::get('post-job/delete/{id}', [PostJobController::class, 'delete']);
        Route::get('post-job/change-status/{id}', [PostJobController::class, 'change_status']);
        Route::post('/get-cities-by-countries', [PostJobController::class, 'getCitiesByCountries'])->name('get.cities.by.countries');
        Route::match(['get', 'post'], 'post-job/applications/{id}', [PostJobController::class, 'applications']);
        Route::get('post-job/user-wise-list', [PostJobController::class, 'userWiseList']);
        Route::match(['get', 'post'], 'post-job/preview/{id}', [PostJobController::class, 'preview']);
        Route::match(['get', 'post'], 'job/view-details/{id}', [PostJobController::class, 'viewDetails']);
        Route::get('post-job/cancel/{id}', [PostJobController::class, 'cancel']);
        Route::get('post-job/approve/{id}', [PostJobController::class, 'approve']);
        Route::get('post-job/reject/{id}', [PostJobController::class, 'reject']);
    /* post job */

    /* upload post job */
        Route::match(['get', 'post'], 'upload-post-job/list', [UploadPostJobController::class, 'list']);
        Route::get('upload-post-job/delete/{id}', [UploadPostJobController::class, 'delete']);
    /* upload post job */

    /* employer users */
        Route::get('employer-user/list', [EmployerUserController::class, 'list']);
        Route::get('employer-user/verified', [EmployerUserController::class, 'verified']);
        Route::get('employer-user/non-verified', [EmployerUserController::class, 'nonVerified']);
        Route::match(['get', 'post'], 'employer-user/add', [EmployerUserController::class, 'add']);
        Route::match(['get', 'post'], 'employer-user/edit/{id}', [EmployerUserController::class, 'edit']);
        Route::get('employer-user/delete/{id}', [EmployerUserController::class, 'delete']);
        Route::get('employer-user/change-status/{id}', [EmployerUserController::class, 'change_status']);
        Route::match(['get', 'post'], 'employer-user/profile/{id}', [EmployerUserController::class, 'profile']);
        Route::match(['get', 'post'], 'employer-user/resend-otp/{id}', [EmployerUserController::class, 'resendOtp']);
        Route::match(['get', 'post'], 'employer-user/verify-otp/{id}', [EmployerUserController::class, 'verifyOtp']);
        Route::match(['get', 'post'], 'employer-user/create-business/{id}', [EmployerUserController::class, 'createBusiness']);
        Route::post('/get-states', [EmployerUserController::class, 'getStates'])->name('get.states');
        Route::post('/get-cities', [EmployerUserController::class, 'getCities'])->name('get.cities');
    /* employer users */

    /* jobseeker */
        Route::get('jobseeker/list', [JobseekerController::class, 'list']);
        Route::match(['get', 'post'], 'jobseeker/add', [JobseekerController::class, 'add']);
        Route::match(['get', 'post'], 'jobseeker/edit/{id}', [JobseekerController::class, 'edit']);
        Route::get('jobseeker/delete/{id}', [JobseekerController::class, 'delete']);
        Route::get('jobseeker/change-status/{id}', [JobseekerController::class, 'change_status']);
        Route::match(['get', 'post'], 'jobseeker/profile/{id}', [JobseekerController::class, 'profile']);
    /* jobseeker */

    /* home page */
        Route::match(['get', 'post'], 'home-page/manage', [HomePageController::class, 'manage']);
    /* home page */
    /* article */
        Route::get('article/list', [ArticleController::class, 'list']);
        Route::match(['get', 'post'], 'article/add', [ArticleController::class, 'add']);
        Route::match(['get', 'post'], 'article/edit/{id}', [ArticleController::class, 'edit']);
        Route::get('article/delete/{id}', [ArticleController::class, 'delete']);
        Route::get('article/change-status/{id}', [ArticleController::class, 'change_status']);
    /* article */
    /* blog */
        Route::get('blog/list', [BlogController::class, 'list']);
        Route::match(['get', 'post'], 'blog/add', [BlogController::class, 'add']);
        Route::match(['get', 'post'], 'blog/edit/{id}', [BlogController::class, 'edit']);
        Route::get('blog/delete/{id}', [BlogController::class, 'delete']);
        Route::get('blog/change-status/{id}', [BlogController::class, 'change_status']);
    /* blog */
    /* testimonial */
        Route::get('testimonial/list', [TestimonialController::class, 'list']);
        Route::match(['get', 'post'], 'testimonial/add', [TestimonialController::class, 'add']);
        Route::match(['get', 'post'], 'testimonial/edit/{id}', [TestimonialController::class, 'edit']);
        Route::get('testimonial/delete/{id}', [TestimonialController::class, 'delete']);
        Route::get('testimonial/change-status/{id}', [TestimonialController::class, 'change_status']);
    /* testimonial */
    /* contact us */
        Route::get('contact-us/list', [ContactUsController::class, 'list']);
    /* contact us */
    /* report bugs */
        Route::get('report-bugs/list', [ReportBugController::class, 'list']);
    /* report bugs */
});
