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
        Schema::create('com_teacher_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacherId')->unique();
            $table->foreign('teacherId')->references('id')->on('users')->onDelete('restrict');
            $table->string('civilStatus')->nullable();
            $table->string('dateOfRetirement')->nullable();
            $table->string('dateOfFirstRegistration')->nullable();
            $table->string('teacherType')->nullable();
            $table->string('teacherGrade')->nullable();
            $table->string('dateOfGrade')->nullable();
            $table->string('salaryType')->nullable();
            $table->string('registerPostNumber')->nullable();
            $table->string('registerPostDate')->nullable();
            $table->string('registerSubject')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_teacher_details');
    }
};
