<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oh_mi_pi_supplier_types', function (Blueprint $table) {
            $table->id();
            $table->string('typeName');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oh_mi_pi_supplier_types');
    }
};
