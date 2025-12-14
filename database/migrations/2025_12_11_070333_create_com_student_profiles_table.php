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
        Schema::create('com_student_profiles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('studentId')->required();
            $table->unsignedBigInteger('academicGradeId')->required();
            $table->unsignedBigInteger('academicClassId')->required();

            $table->string('academicYear')->required();
            $table->string('academicMedium')->required();

            $table->foreign('studentId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('academicGradeId')->references('id')->on('com_grades')->onDelete('restrict');
            $table->foreign('academicClassId')->references('id')->on('com_class_mngs')->onDelete('restrict');

            $table->boolean('isStudentApproved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_student_profiles');
    }
};
