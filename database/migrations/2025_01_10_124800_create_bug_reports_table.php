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
        Schema::create('bug_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('companies')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->references('id')
                ->cascadeOnDelete();
            $table->string('title');
            $table->boolean('status')->default(true);
            $table->longText('message');
            $table->enum('platform', ['web', 'android', 'ios'])->default('android');
            $table->string('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_reports');
    }
};
