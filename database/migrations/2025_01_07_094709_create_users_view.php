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
            CREATE VIEW users_view AS
            SELECT
            u.*, c.name as company, c.latitude as company_lat, c.longitude as company_long, c.radius as company_radius, c.full_address as company_address, us.basic_salary, us.payment_type, ue.bank_name, ue.bank_number, ue.bank_holder, ud.phone, ud.placebirth, ud.datebirth, ud.gender, ud.blood, ud.marital_status, ud.religion,
            ua.identity_type, ua.identity_numbers, ua.province, ua.city, ua.citizen_address, ua.residential_address,
            ue.departement_id, ue.job_position_id, ue.job_level_id, ue.approval_line_id, ue.approval_manager_id, ue.join_date, ue.sign_date, ue.resign_date,
            d.name as departement, jp.name as position, jl.name as level
            FROM users u
            JOIN user_salaries us ON u.id=us.user_id
            JOIN user_details ud ON u.id=ud.user_id
            JOIN user_address ua ON u.id=ua.user_id
            JOIN user_employes ue ON u.id=ue.user_id
            JOIN departements d ON ue.departement_id = d.id
            JOIN job_positions jp ON ue.job_position_id=jp.id
            JOIN job_levels jl ON ue.job_level_id=jl.id
            JOIN companies c ON u.company_id = c.id;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS users_view");
    }
};
