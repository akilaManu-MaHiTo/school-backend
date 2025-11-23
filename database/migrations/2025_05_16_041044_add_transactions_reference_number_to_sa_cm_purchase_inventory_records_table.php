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
        Schema::table('sa_cm_purchase_inventory_records', function (Blueprint $table) {
            $table->string('transactionsRefferenceNumber')->nullable()->after('lotNumber');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sa_cm_purchase_inventory_records', function (Blueprint $table) {
            $table->dropColumn('transactionsRefferenceNumber');
        });
    }
};
