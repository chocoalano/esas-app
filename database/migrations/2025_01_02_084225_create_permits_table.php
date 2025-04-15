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
        Schema::create('permit_types', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100)->unique();
            $table->boolean('is_payed')->default(true);
            $table->boolean('approve_line')->default(true);
            $table->boolean('approve_manager')->default(true);
            $table->boolean('approve_hr')->default(true);
            $table->boolean('show_mobile')->default(true);
            $table->boolean('with_file')->default(true);
            $table->timestamps();
        });
        Schema::create('permits', function (Blueprint $table) {
            $table->id();
            $table->string('permit_numbers', 100)->unique();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreignId('permit_type_id')
                ->constrained('permit_types')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreignId('user_timework_schedule_id')
                ->constrained('user_timework_schedules')
                ->references('id')
                ->cascadeOnDelete();
            $table->time('timein_adjust')->nullable();
            $table->time('timeout_adjust')->nullable();
            $table->foreignId('current_shift_id')->nullable();
            $table->foreignId('adjust_shift_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->longText('notes')->nullable();
            $table->string('file');
            $table->timestamps();
        });
        Schema::create('permit_approves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permit_id')
                ->constrained('permits')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('user_type');
            $table->enum('user_approve', ['w', 'n', 'y'])->default('w');
            $table->longText('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_approves');
        Schema::dropIfExists('permits');
        Schema::dropIfExists('permit_types');
    }
};
