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
        Schema::create('com_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('organizationName');
            $table->string('logoUrl')->nullable();
            $table->json('colorPallet')->nullable();
            $table->string('insightImage')->nullable();
            $table->string('insightDescription')->nullable();
            $table->string('status')->nullable();
            $table->integer('createdByUser')->nullable();
            $table->integer('updatedBy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_organizations');
    }
};
