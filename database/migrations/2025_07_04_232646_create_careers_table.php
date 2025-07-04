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
        Schema::create('careers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id')->comment('ID del departamento al que pertenece');
            $table->string('name')->comment('Nombre de la carrera');
            $table->string('code')->nullable()->comment('Código de la carrera');
            $table->timestamps();
            $table->softDeletes();

            // Campos de auditoría para microservicios
            $table->string('created_by')->nullable()->comment('Usuario que creó el registro');
            $table->string('updated_by')->nullable()->comment('Usuario que actualizó el registro');
            $table->integer('version')->default(1)->comment('Versión del registro para concurrencia optimista');

            // Relaciones
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');

            // Índices
            $table->index(['department_id']);
            $table->index(['deleted_at']);
            $table->index(['code']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};
