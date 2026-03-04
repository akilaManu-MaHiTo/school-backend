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
        Schema::create('com_payment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('categoryName');
            $table->unsignedBigInteger('createdBy');
            $table->timestamps();

            $table->foreign('createdBy')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_payment_categories');
    }
};
