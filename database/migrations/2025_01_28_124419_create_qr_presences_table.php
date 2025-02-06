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
        // Tabel utama: qr_presences
        Schema::create('qr_presences', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['in', 'out'])->default('in');
            $table->foreignId('departement_id')
                ->constrained('departements')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreignId('timework_id')
                ->constrained('time_workes')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('token')->unique(); // Token unik
            $table->dateTime('for_presence'); // Waktu presensi
            $table->timestamp('expires_at'); // Waktu kedaluwarsa
            $table->timestamps(); // Kolom created_at dan updated_at
        });

        // Tabel transaksi: qr_presence_transactions
        Schema::create('qr_presence_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('qr_presence_id'); // Foreign key ke qr_presences
            $table->unsignedBigInteger('user_attendance_id'); // Foreign key ke user_attendance
            $table->string('token'); // Token unik untuk setiap transaksi
            $table->timestamps(); // Kolom created_at dan updated_at

            // Menambahkan relasi foreign key
            $table->foreign('qr_presence_id')
                ->references('id')
                ->on('qr_presences')
                ->onDelete('cascade'); // Hapus transaksi jika qr_presences dihapus

            $table->foreign('user_attendance_id')
                ->references('id')
                ->on('user_attendances') // Asumsi tabel bernama user_attendances
                ->onDelete('cascade'); // Hapus transaksi jika user_attendance dihapus
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus tabel yang memiliki foreign key terlebih dahulu
        Schema::dropIfExists('qr_presence_transactions');
        Schema::dropIfExists('qr_presences');
    }
};
