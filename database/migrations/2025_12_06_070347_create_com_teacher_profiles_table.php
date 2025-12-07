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
        Schema::create('com_teacher_profiles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('teacherId')->required();
            $table->unsignedBigInteger('academicGradeId')->required();
            $table->unsignedBigInteger('academicSubjectId')->required();
            $table->unsignedBigInteger('academicClassId')->required();

            $table->string('academicYear')->required();
            $table->string('academicMedium')->required();

            $table->foreign('teacherId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('academicGradeId')->references('id')->on('com_grades')->onDelete('restrict');
            $table->foreign('academicSubjectId')->references('id')->on('com_subjects')->onDelete('restrict');
            $table->foreign('academicClassId')->references('id')->on('com_class_mngs')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_teacher_profiles');
    }
};
