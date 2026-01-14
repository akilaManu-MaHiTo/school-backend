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
        Schema::create('com_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subjectCode')->nullable();
            $table->string('subjectMedium')->required();
            $table->string('subjectName')->required();
            $table->string('colorCode')->required();
            $table->boolean('isBasketSubject');
            $table->enum('basketGroup', ['Group 1', 'Group 2', 'Group 3'])->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_subjects');
    }
};
