<?php

namespace App\Http\Controllers;
use App\Repositories\Interfaces\AdministrationApp\ScheduleAttendanceInterface;

class TemplateImportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function schedule()
    {
        return app(ScheduleAttendanceInterface::class)->template();
    }
}
