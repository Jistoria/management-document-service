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
        Schema::create('metadata_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('schema_id');
            $table->string('name'); // nombre técnico
            $table->string('data_type'); // STRING, INT, DATE, UUID, etc.
            $table->boolean('is_required')->default(false);
            $table->text('default_value')->nullable();
            $table->string('validation_regex')->nullable();
            $table->integer('field_order')->nullable();

            $table->jsonb('lookup_keywords')->nullable();   // ["fecha emisión", "emitido", "f. doc", "fecha del documento"]
            $table->string('ocr_hint')->nullable();         // "DD/MM/YYYY", "MAYÚSCULA", "2 palabras", etc.
            $table->boolean('ignore_in_similarity')->default(false); // si este campo no se usa para comparar duplicados


            // soporte relacional
            $table->boolean('is_reference')->default(false);
            $table->string('reference_entity')->nullable(); // tabla destino
            $table->string('reference_column')->default('id'); // columna en destino

            $table->timestamps();

            $table->foreign('schema_id')->references('id')->on('metadata_schemas')->onDelete('cascade');

            // CHECK de consistencia
            $table->check(
                "((is_reference = false AND reference_entity IS NULL) OR (is_reference = true AND reference_entity IS NOT NULL))"
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metadata_fields');
    }
};
