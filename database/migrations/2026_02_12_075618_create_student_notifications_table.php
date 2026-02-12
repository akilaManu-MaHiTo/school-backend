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
        Schema::create('student_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('createdBy');
            $table->string('title');
            $table->string('description');
            $table->string('year');
            $table->unsignedBigInteger('gradeId');
            $table->unsignedBigInteger('classId');
            $table->json('ignoreUserIds')->nullable();
            $table->timestamps();

            $table->foreign('createdBy')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('gradeId')->references('id')->on('com_grades')->onDelete('cascade');
            $table->foreign('classId')->references('id')->on('com_class_mngs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_notifications');
    }
};
