<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('metadata_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('field_key', 255);
            $table->string('label', 255);
            $table->uuid('entity_type_id')->nullable();
            $table->string('type_input_id', 255);
            $table->string('data_type', 255);
            $table->boolean('is_reference')->default(false);
            $table->string('reference_entity')->nullable();
            $table->string('reference_column')->default('id');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('field_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metadata_fields');
    }
};
