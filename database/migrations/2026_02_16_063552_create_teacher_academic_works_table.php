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
        Schema::create('teacher_academic_works', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacherId');
            $table->unsignedBigInteger('subjectId');
            $table->string('title');
            $table->string('academicWork');
            $table->string('date');
            $table->string('time');
            $table->boolean('approved')->default(false);
            $table->unsignedBigInteger('createdBy');


            $table->foreign('teacherId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subjectId')->references('id')->on('com_subjects')->onDelete('cascade');
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_academic_works');
    }
};
