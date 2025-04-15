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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('latitude')->unique();
            $table->string('longitude')->unique();
            $table->string('radius')->unique();
            $table->string('full_address')->unique();
            $table->timestamps();
        });
        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->references('id')
                ->cascadeOnDelete()
            ;
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('time_workes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreignId('departemen_id')
                ->constrained('departements')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('name');
            $table->time('in');
            $table->time('out');
            $table->timestamps();
        });
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreignId('departement_id')
                ->constrained('departements')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('job_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreignId('departement_id')
                ->constrained('departements')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('nip')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar');
            $table->enum('status', ['active', 'inactive', 'resign'])->default('active');
            $table->rememberToken();
            $table->string('device_id')->nullable();
            $table->timestamps();
        });
        Schema::create('user_timework_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreignId('time_work_id')
                ->constrained('time_workes')
                ->references('id')
                ->cascadeOnDelete();
            $table->date('work_day');
            $table->timestamps();
        });
        Schema::create('user_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreignId('user_timework_schedule_id')
                ->nullable()
                ->index();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->enum('type_in', ['qrcode', 'face-device', 'face-geolocation'])->default('qrcode');
            $table->enum('type_out', ['qrcode', 'face-device', 'face-geolocation'])->default('qrcode');
            $table->string('lat_in', 100)->nullable();
            $table->string('lat_out', 100)->nullable();
            $table->string('long_in', 100)->nullable();
            $table->string('long_out', 100)->nullable();
            $table->string('image_in')->nullable();
            $table->string('image_out')->nullable();
            $table->enum('status_in', ['late', 'unlate', 'normal'])->default('normal');
            $table->enum('status_out', ['late', 'unlate', 'normal'])->default('normal');
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('phone', 50)->unique();
            $table->string('placebirth', 100);
            $table->date('datebirth');
            $table->enum('gender', ['m', 'w'])->default('m');
            $table->enum('blood', ['a', 'b', 'o', 'ab'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'widow', 'widower'])->nullable();
            $table->enum('religion', ['islam', 'protestan', 'khatolik', 'hindu', 'buddha', 'khonghucu'])->nullable();
            $table->timestamps();
        });
        Schema::create('user_employes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreignId('departement_id')->nullable()->index();
            $table->foreignId('job_position_id')->nullable()->index();
            $table->foreignId('job_level_id')->nullable()->index();
            $table->foreignId('approval_line_id')->nullable()->index();
            $table->foreignId('approval_manager_id')->nullable()->index();
            $table->date('join_date');
            $table->date('sign_date');
            $table->date('resign_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_number')->nullable();
            $table->string('bank_holder')->nullable();
            $table->integer('saldo_cuti')->nullable();
            $table->timestamps();
        });
        Schema::create('user_address', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->enum('identity_type', ['ktp', 'sim', 'passport'])->default('ktp');
            $table->string('identity_numbers', 100)->unique();
            $table->string('province', 100);
            $table->string('city', 100);
            $table->longText('citizen_address');
            $table->longText('residential_address');
            $table->timestamps();
        });
        Schema::create('user_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->decimal('basic_salary', 20, 2)->default(0.00);
            $table->enum('payment_type', ['Monthly', 'Weekly', 'Daily'])->default('Monthly');
            $table->timestamps();
        });
        Schema::create('user_families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('fullname', 100);
            $table->enum('relationship', ['wife', 'husband', 'mother', 'father', 'brother', 'sister', 'child'])->default('wife');
            $table->date('birthdate');
            $table->enum('marital_status', ['single', 'married', 'widow', 'widower'])->nullable();
            $table->string('job', 100);
            $table->timestamps();
        });
        Schema::create('user_formal_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('institution', 100);
            $table->string('majors', 100);
            $table->decimal('score', 3,2)->default(0.00);
            $table->year('start')->nullable();
            $table->year('finish')->nullable();
            $table->enum('status', ['passed', 'not-passed', 'in-progress']);
            $table->boolean('certification')->default(true);
            $table->timestamps();
        });
        Schema::create('user_informal_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('institution', 100);
            $table->year('start')->nullable();
            $table->year('finish')->nullable();
            $table->enum('type', ['day', 'year', 'month'])->default('day');
            $table->integer('duration')->default(1);
            $table->enum('status', ['passed', 'not-passed', 'in-progress']);
            $table->boolean('certification')->default(true);
            $table->timestamps();
        });
        Schema::create('user_work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('company_name', 100);
            $table->year('start')->nullable();
            $table->year('finish')->nullable();
            $table->string('position', 100)->nullable();
            $table->boolean('certification')->default(true);
            $table->timestamps();
        });
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_work_experiences');
        Schema::dropIfExists('user_informal_educations');
        Schema::dropIfExists('user_formal_educations');
        Schema::dropIfExists('user_families');
        Schema::dropIfExists('user_salaries');
        Schema::dropIfExists('user_address');
        Schema::dropIfExists('user_employes');
        Schema::dropIfExists('user_details');
        Schema::dropIfExists('user_attendances');
        Schema::dropIfExists('user_timework_schedules');
        Schema::dropIfExists('users');
        Schema::dropIfExists('job_levels');
        Schema::dropIfExists('job_positions');
        Schema::dropIfExists('time_workes');
        Schema::dropIfExists('departements');
        Schema::dropIfExists('companies');
    }
};
