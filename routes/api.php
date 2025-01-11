<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\PermitController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::controller(UserController::class)
        ->group(function () {
            Route::post('register', 'register');
            Route::post('login', 'login');
        });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::controller(UserController::class)
            ->group(function () {
                Route::get('profile', 'profile');
                Route::get('profile-permission', 'profile_permission');
                Route::post('profile-update', 'profile_update');
                Route::post('profile-update-family', 'profile_update_family');
                Route::post('profile-update-formal-education', 'profile_update_formal_education');
                Route::post('profile-update-informal-education', 'profile_update_informal_education');
                Route::post('profile-update-work-experience', 'profile_update_work_experience');
                Route::post('profile-update-password', 'profile_update_password');
                Route::get('profile-avatar', 'profile_avatar');
                Route::post('logout', 'logout');
            });

    });
    // Route::resource('user', UserController::class);
    Route::resources([
        // 'roles' => RoleController::class,
        'user' => UserController::class,
        'permit' => PermitController::class,
        'attendance' => AttendanceController::class,
        'announcement' => AnnouncementController::class,
    ]);
});
