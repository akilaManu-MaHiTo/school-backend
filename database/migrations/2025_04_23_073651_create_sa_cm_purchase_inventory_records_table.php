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
        Schema::create('sa_cm_purchase_inventory_records', function (Blueprint $table) {
            $table->id();
            $table->string('referenceNumber')->nullable();
            $table->string('inventoryNumber')->nullable();
            $table->string('commercialName')->nullable();
            $table->string('substanceName')->nullable();
            $table->string('reachRegistrationNumber')->nullable();
            $table->string('molecularFormula')->nullable();
            $table->integer('requestQuantity')->nullable();
            $table->string('requestUnit')->nullable();
            $table->string('zdhcCategory')->nullable();
            $table->string('chemicalFormType')->nullable();
            $table->string('whereAndWhyUse')->nullable();
            $table->string('productStandard')->nullable();
            $table->string('doYouHaveMSDSorSDS')->nullable();
            $table->string('msdsorsdsIssuedDate')->nullable();
            $table->string('msdsorsdsExpiryDate')->nullable();
            $table->json('documents')->nullable();
            $table->string('division')->nullable();
            $table->string('requestedCustomer')->nullable();
            $table->string('requestedMerchandiser')->nullable();
            $table->string('requestDate')->nullable();
            $table->string('reviewerId')->nullable();
            $table->string('approverId')->nullable();
            $table->json('hazardType')->nullable();
            $table->json('useOfPPE')->nullable();
            $table->string('ghsClassification')->nullable();
            $table->string('zdhcLevel')->nullable();
            $table->string('casNumber')->nullable();
            $table->string('colourIndex')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'published', 'ongoing', 'draft', 'scheduled', 'completed', 'reviewed'])->default('draft')->nullable();
            $table->string('createdByUser')->nullable();
            $table->string('updatedBy')->nullable();
            $table->string('approvedBy')->nullable();
            $table->string('rejectedBy')->nullable();
            $table->string('responsibleSection')->nullable()->default('ChemicalManagement');
            $table->string('assigneeLevel')->nullable()->default('1');
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('manufactureName')->nullable();
            $table->string('contactNumber')->nullable();
            $table->string('emailId')->nullable();
            $table->string('location')->nullable();
            $table->string('compliantWithTheLatestVersionOfZDHCandMRSL')->nullable();
            $table->string('apeoOrNpeFreeComplianceStatement')->nullable();
            $table->string('manufacturingDate')->nullable();
            $table->string('expiryDate')->nullable();
            $table->string('deliveryDate')->nullable();
            $table->integer('deliveryQuantity')->nullable(); 
            $table->string('deliveryUnit')->nullable();
            $table->integer('purchaseAmount')->nullable();
            $table->string('thresholdLimit')->nullable();
            $table->string('invoiceDate')->nullable();
            $table->string('invoiceReference')->nullable();
            $table->string('hazardStatement')->nullable();
            $table->string('storageConditionRequirements')->nullable();
            $table->string('storagePlace')->nullable();
            $table->string('lotNumber')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sa_cm_purchase_inventory_records');
    }
};
