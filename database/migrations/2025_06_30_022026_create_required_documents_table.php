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
        Schema::create('required_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('process_id');
            $table->uuid('document_type_id');
            $table->uuid('academic_role_id')->nullable(); // rol requerido (opcional)
            $table->integer('order')->default(0);
            $table->boolean('mandatory')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
            $table->foreign('academic_role_id')->references('id')->on('academic_roles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('required_documents');
    }
};
