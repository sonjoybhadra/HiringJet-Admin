<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [UserController::class, 'login'])->name('login');
Route::post('signin', [UserController::class, 'login'])->name('signin');
Route::match(['get','post'],'/forgot-password', [UserController::class, 'forgotPassword']);
Route::match(['get','post'],'/validate-otp/{id}', [UserController::class, 'validateOtp']);
Route::match(['get','post'],'/resend-otp/{id}', [UserController::class, 'resendOtp']);
Route::match(['get','post'],'/reset-password/{id}', [UserController::class, 'resetPassword']);

Route::middleware(['auth'])->group(function () {
	Route::get('dashboard', [UserController::class, 'dashboard']);
	Route::get('logout', [UserController::class, 'logout']);
	Route::get('email-logs', [UserController::class, 'emailLogs']);
    Route::match(['get','post'],'/email-logs/details/{email}', [UserController::class, 'emailLogsDetails']);
    Route::get('login-logs', [UserController::class, 'loginLogs']);
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
            Route::get('admin-users/list', [AdminUserController::class, 'list']);
            Route::match(['get', 'post'], 'admin-users/add', [AdminUserController::class, 'add']);
            Route::match(['get', 'post'], 'admin-users/edit/{id}', [AdminUserController::class, 'edit']);
            Route::get('admin-users/delete/{id}', [AdminUserController::class, 'delete']);
            Route::get('admin-users/change-status/{id}', [AdminUserController::class, 'change_status']);
        /* admin users */
    /* access & permission */
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
    /* package */
        Route::get('page/list', [PackageController::class, 'list']);
        Route::match(['get', 'post'], 'page/add', [PackageController::class, 'add']);
        Route::match(['get', 'post'], 'page/edit/{id}', [PackageController::class, 'edit']);
        Route::get('page/delete/{id}', [PackageController::class, 'delete']);
        Route::get('page/change-status/{id}', [PackageController::class, 'change_status']);
    /* package */
});