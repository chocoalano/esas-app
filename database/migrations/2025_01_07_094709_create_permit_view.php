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
            CREATE VIEW permit_view AS
            SELECT
                p.*,
                pt.type,
                pt.is_payed,
                pt.approve_line,
                pt.approve_manager,
                pt.approve_hr,
                u.name AS user_name,
                u.nip,
                d.name AS departement_name
            FROM permits p
            JOIN permit_types pt ON p.permit_type_id = pt.id
            JOIN users u ON p.user_id = u.id
            JOIN user_employes ue ON u.id = ue.user_id
            JOIN departements d ON ue.departement_id = d.id;
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
