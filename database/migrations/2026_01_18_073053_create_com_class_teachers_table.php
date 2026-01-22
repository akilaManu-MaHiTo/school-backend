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
        Schema::create('com_class_teachers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('classId');
            $table->unsignedBigInteger('teacherId');
            $table->string('gradeId');
            $table->string(column: 'year');

            $table->foreign('classId')->references('id')->on('com_class_mngs')->onDelete('restrict');
            $table->foreign('teacherId')->references('id')->on('users')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_class_teachers');
    }
};
