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
        Schema::create('head_offices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->comment('Nombre de la sede');
            $table->string('code')->unique()->comment('Código único de la sede');
            $table->timestamps();
            $table->softDeletes();

            // Campos de auditoría para microservicios
            $table->string('created_by')->nullable()->comment('Usuario que creó el registro');
            $table->string('updated_by')->nullable()->comment('Usuario que actualizó el registro');
            $table->integer('version')->default(1)->comment('Versión del registro para concurrencia optimista');

            // Índices
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
        Schema::dropIfExists('head_offices');
    }
};
