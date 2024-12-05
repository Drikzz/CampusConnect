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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('wmsu_email')->nullable()->unique();

            // based on user type on registration
            $table->foreignId('user_type_id')->nullable()->constrained('user_types')->onDelete('restrict');
            $table->foreignId('wmsu_dept_id')->nullable()->constrained('departments')->onDelete('restrict');
            $table->foreignId('grade_level_id')->nullable()->constrained('grade_levels')->onDelete('restrict');

            $table->string('profile_picture')->nullable();
            $table->timestamp('email_verified_at')->nullable();

            // user verification
            $table->boolean('is_seller')->default(false);
            $table->string('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            // If this user is an admin
            $table->timestamp('is_admin')->default(false);


            $table->string('wmsu_id_front')->nullable();
            $table->string('wmsu_id_back')->nullable();

            // no rating yets
            $table->rememberToken();
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
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
