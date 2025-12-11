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
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('studentProfileId')->required();
            $table->unsignedBigInteger('academicSubjectId')->required();

            $table->string('studentMark')->nullable();
            $table->string('markGrade')->nullable();
            $table->string('academicYear')->nullable();
            $table->string('academicTerm')->nullable();

            $table->foreign('studentProfileId')->references('id')->on('com_student_profiles')->onDelete('restrict');
            $table->foreign('academicSubjectId')->references('id')->on('com_subjects')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_marks');
    }
};
