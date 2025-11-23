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
        Schema::create('sa_cm_cmr_commercial_names', function (Blueprint $table) {
            $table->id();
            $table->string('commercialName')->nullable();
            $table->string('substanceName')->nullable();
            $table->string('molecularFormula')->nullable();
            $table->string('chemicalFormType')->nullable();
            $table->string('reachRegistrationNumber')->nullable();
            $table->string('whereAndWhyUse')->nullable();
            $table->string('zdhcCategory')->nullable();
            $table->string('zdhcLevel')->nullable();
            $table->string('casNumber')->nullable();
            $table->string('colourIndex')->nullable();
            $table->json('useOfPPE')->nullable();
            $table->json('hazardType')->nullable();
            $table->string('ghsClassification')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sa_cm_cmr_commercial_names');
    }
};
