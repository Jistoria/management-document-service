<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('metadata_schema_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('metadata_schema_id');
            $table->uuid('metadata_field_id');
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->nullable();
            $table->text('default_value')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('metadata_schema_id')->references('id')->on('metadata_schemas')->onDelete('cascade');
            $table->foreign('metadata_field_id')->references('id')->on('metadata_fields')->onDelete('cascade');
            $table->unique(['metadata_schema_id', 'metadata_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metadata_schema_fields');
    }
};
