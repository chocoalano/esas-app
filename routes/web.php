<?php

use App\Events\FcmNotification;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\TemplateImportController;
use App\Support\NotificationService;
use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Messaging\CloudMessage;

Route::get('/', [QrCodeController::class, 'index'])
    ->middleware('mac.restrict')
    ->name('index');
Route::get('/face-recognition', [QrCodeController::class, 'face_recognition'])
    ->middleware('mac.restrict')
    ->name('face-recognition');
Route::post('/face-recognition', [QrCodeController::class, 'face_recognition_store'])
    ->middleware('mac.restrict')
    ->name('face-recognition-store');

Route::get('template-schedule', [TemplateImportController::class, 'schedule'])
    ->name('template.schedule');

Route::get('/secure-download/{filename}', function ($filename) {
    $filePath = storage_path("app/private/{$filename}");

    if (!file_exists($filePath)) {
        abort(404, "File tidak ditemukan.");
    }

    return Response::download($filePath);
});

Route::get('send-notification', [App\Http\Controllers\FirebaseImplementController::class, 'index']);
