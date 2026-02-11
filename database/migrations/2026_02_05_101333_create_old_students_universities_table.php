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
        Schema::create('old_students_universities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('studentId');
            $table->string('universityName');
            $table->string('country');
            $table->string('city');
            $table->string('degree');
            $table->string('faculty');
            $table->string('yearOfAdmission');
            $table->string('yearOfGraduation');
            $table->timestamps();

            $table->foreign('studentId')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('old_students_universities');
    }
};
