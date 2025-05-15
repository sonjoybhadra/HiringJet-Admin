<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgotpasswordController;
use App\Http\Controllers\Api\ResumeParserController;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [ForgotpasswordController::class, 'forgot_password']);
Route::post('/forgot-password/otp-verification', [ForgotpasswordController::class, 'otp_verification']);
Route::post('/forgot-password/reset_password', [ForgotpasswordController::class, 'reset_password']);

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
