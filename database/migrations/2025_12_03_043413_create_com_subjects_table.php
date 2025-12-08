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
            $table->boolean('isBasketSubject');
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
