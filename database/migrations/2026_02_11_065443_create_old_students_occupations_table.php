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
        Schema::create('old_students_occupations', function (Blueprint $table) {
            $table->id();
            $table->string('companyName');
            $table->string('occupation');
            $table->string('description')->nullable();
            $table->string('dateOfRegistration');
            $table->string('country');
            $table->string('city');
            $table->unsignedBigInteger('studentId');
            $table->timestamps();

            $table->foreign('studentId')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('old_students_occupations');
    }
};
