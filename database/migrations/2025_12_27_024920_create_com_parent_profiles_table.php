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
        Schema::create('com_parent_profiles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('parentId')->required();
            $table->unsignedBigInteger('studentProfileId')->required();

            $table->foreign('parentId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('studentProfileId')->references('id')->on('com_student_profiles')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_parent_profiles');
    }
};
