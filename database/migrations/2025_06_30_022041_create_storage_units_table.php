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
        Schema::create('storage_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('storage_unit_type_id');
            $table->uuid('parent_id')->nullable();
            $table->string('label');
            $table->string('code')->nullable(); // identificador interno opcional
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('storage_unit_type_id')->references('id')->on('storage_unit_types')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('storage_units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_units');
    }
};
