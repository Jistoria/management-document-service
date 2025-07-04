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
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('head_office_id')->comment('ID de la sede a la que pertenece');
            $table->string('name')->comment('Nombre del departamento');
            $table->string('code')->nullable()->comment('Código del departamento');
            $table->timestamps();
            $table->softDeletes();

            // Campos de auditoría para microservicios
            $table->string('created_by')->nullable()->comment('Usuario que creó el registro');
            $table->string('updated_by')->nullable()->comment('Usuario que actualizó el registro');
            $table->integer('version')->default(1)->comment('Versión del registro para concurrencia optimista');

            // Relaciones
            $table->foreign('head_office_id')->references('id')->on('head_offices')->onDelete('cascade');

            // Índices
            $table->index(['head_office_id']);
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
        Schema::dropIfExists('departments');
    }
};
