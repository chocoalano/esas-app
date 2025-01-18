<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permit_types', function (Blueprint $table) {
            $table->boolean('show_mobile')->default(false)->after('approve_hr');
            $table->boolean('with_file')->default(false)->after('approve_hr');
        });
        Schema::table('permits', function (Blueprint $table) {
            $table->string('file')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permit_types', function (Blueprint $table) {
            $table->dropColumn('show_mobile');
            $table->dropColumn('with_file');
        });
        Schema::table('permits', function (Blueprint $table) {
            $table->dropColumn('file');
        });
    }
};
