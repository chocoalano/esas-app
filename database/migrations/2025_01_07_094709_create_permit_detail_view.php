<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE VIEW permit_detail_view AS
            SELECT
                p.*,
                pt.type,
                pt.is_payed,
                pt.approve_line,
                pt.approve_manager,
                pt.approve_hr,
                pt.with_file,
                c.name AS company,
                u.name AS user_name,
                u.nip,
                d.name AS departement,
                jp.name AS position,
                jl.name AS levels
            FROM
                permits p
            JOIN
                permit_types pt ON p.permit_type_id = pt.id
            JOIN
                users u ON p.user_id = u.id
            JOIN
                companies c ON u.company_id = c.id
            LEFT JOIN
                user_employes ue ON ue.user_id = u.id
            LEFT JOIN
                departements d ON ue.departement_id = d.id
            LEFT JOIN
                job_positions jp ON ue.job_position_id = jp.id
            LEFT JOIN
                job_levels jl ON ue.job_level_id = jl.id;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS permit_detail_view");
    }
};
