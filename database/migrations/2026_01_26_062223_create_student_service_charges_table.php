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
        Schema::create('student_service_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('studentId');
            $table->string('chargesCategory');
            $table->decimal('amount', 8, 2);
            $table->string('dateCharged');
            $table->integer('yearForCharge')->required();
            $table->string('remarks')->nullable();
            $table->timestamps();


            $table->foreign('studentId')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_service_charges');
    }
};
