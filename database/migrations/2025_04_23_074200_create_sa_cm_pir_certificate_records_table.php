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
        Schema::create('sa_cm_pir_certificate_records', function (Blueprint $table) {
            $table->id('certificateId');
            $table->bigInteger('inventoryId')->nullable();
            $table->string('testName')->nullable();
            $table->string('testDate')->nullable();
            $table->string('testLab')->nullable();
            $table->string('issuedDate')->nullable();
            $table->string('expiryDate')->nullable();
            $table->string('positiveList')->nullable();
            $table->string('description')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sa_cm_pir_certificate_records');
    }
};
