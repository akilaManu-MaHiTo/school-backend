<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('userName')->unique();
            $table->string('nameWithInitials')->required();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->enum('employeeType', ['Student', 'Teacher', 'Parent'])->default('Student');
            $table->string('employeeNumber')->nullable()->unique();
            $table->string('mobile')->nullable()->unique();
            $table->rememberToken();
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('emailVerifiedAt')->default(false);
            $table->unsignedBigInteger('userType')->nullable()->default(2);
            $table->foreign('userType')->references('id')->on('com_permissions')->onDelete('restrict');
            $table->integer('assigneeLevel')->default(2)->nullable();
            $table->json('profileImage')->nullable();
            $table->boolean('availability')->default(true);
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('birthDate')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email');
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            $table->foreign('email')->references('email')->on('users')->onDelete('cascade');
            $table->primary('email');
        });

        // Create the sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index()->constrained('users')->onDelete('cascade');
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
        Schema::dropIfExists('com_users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
