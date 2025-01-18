<?php

use App\Http\Controllers\TemplateImportController;
use App\Support\NotificationService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/app');
});
Route::get('template-schedule', [TemplateImportController::class, 'schedule'])->name('template.schedule');
Route::get('/notify', function () {
    NotificationService::sendNotification(
        'Informasi permintaan',
        'Testing pemberitahuan bosskuh',
        null,
        94
    );
    return 'Notification Sent!';
});
