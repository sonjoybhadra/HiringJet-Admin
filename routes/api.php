<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgotpasswordController;

use App\Http\Controllers\Api\ResumeParserController;
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
Route::get('/get-industry', [CommonController::class, 'get_industry']);
Route::get('/get-country', [CommonController::class, 'get_country']);
Route::get('/get-country-code', [CommonController::class, 'get_country_code']);
Route::get('/get-nationality', [CommonController::class, 'get_nationality']);
Route::get('/get-religion', [CommonController::class, 'get_religion']);
Route::get('/get-university', [CommonController::class, 'get_university']);
Route::get('/get-qualification', [CommonController::class, 'get_qualification']);
Route::get('/get-language', [CommonController::class, 'get_language']);
Route::get('/get-designation', [CommonController::class, 'get_designation']);
Route::get('/get-keyskill', [CommonController::class, 'get_keyskill']);
Route::get('/get-proficiency-level', [CommonController::class, 'get_proficiency_level']);


Route::group([
    'middleware' => ['auth:api'],
], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'getUser']);
    Route::post('/change-password', [AuthController::class, 'change_password']);
    Route::post('/update-profile', [AuthController::class, 'update_profile']);

});

Route::post('/parse-resume', [ResumeParserController::class, 'parse']);


/* Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); */
