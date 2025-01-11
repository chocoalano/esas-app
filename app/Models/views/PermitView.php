<?php

namespace App\Models\views;

use Illuminate\Database\Eloquent\Model;

class PermitView extends Model
{
    protected $table = 'permit_view';
    protected $fillable=[
        'id',
        'permit_numbers',
        'user_id',
        'permit_type_id',
        'user_timework_schedule_id',
        'timein_adjust',
        'timeout_adjust',
        'current_shift_id',
        'adjust_shift_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'notes',
        'type',
        'is_payed',
        'approve_line',
        'approve_manager',
        'approve_hr',
        'user_name',
        'nip',
        'departement_name',
    ];
}
