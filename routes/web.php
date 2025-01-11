<?php

use App\Events\MessageSent;
use App\Http\Controllers\TemplateImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/app');
});
Route::get('template-schedule', [TemplateImportController::class, 'schedule'])->name('template.schedule');
Route::get('/notify', function () {
    $url = route('filament.app.resources.companies.index');
    broadcast(new MessageSent('Hello from Filament!', 1, 1, $url));
    return 'Notification Sent!';
});
