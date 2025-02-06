<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete(); // Tidak perlu references() karena default akan mengarah ke 'id'

            // Menggunakan nilai boolean yang benar untuk default
            $table->boolean('attendance_image_geolocation')->default(false);
            $table->boolean('attendance_qrcode')->default(false);
            $table->boolean('attendance_fingerprint')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
