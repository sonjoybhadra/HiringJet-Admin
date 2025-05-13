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
});