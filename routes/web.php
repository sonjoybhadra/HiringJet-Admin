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
});