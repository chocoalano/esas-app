<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE VIEW attendance_view AS
                SELECT
                ua.id,
                u.id as user_id, u.company_id, u.name, u.nip, u.avatar,
                ue.departement_id, ue.job_position_id, ue.job_level_id, ue.approval_line_id, ue.approval_manager_id, ue.join_date, ue.sign_date,
                d.name as departement, jp.name as position, jl.name as level,
                uts.work_day, tw.name as shiftname, tw.in, tw.out,
                ua.user_timework_schedule_id, ua.time_in, ua.lat_in, ua.long_in, ua.image_in, ua.status_in,
                ua.time_out, ua.lat_out, ua.long_out, ua.image_out, ua.status_out, ua.created_at, ua.updated_at
                FROM users u
                JOIN user_employes ue ON u.id = ue.user_id
                JOIN departements d ON ue.departement_id = d.id
                JOIN job_positions jp ON ue.job_position_id = jp.id
                JOIN job_levels jl ON ue.job_level_id = jl.id
                JOIN user_attendances ua ON u.id=ua.user_id
                JOIN companies c ON u.company_id = c.id
                LEFT JOIN user_timework_schedules uts ON uts.id = ua.user_timework_schedule_id
                LEFT JOIN time_workes tw ON tw.id = uts.time_work_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS permit_view");
    }
};
