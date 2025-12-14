<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('md_auth_users', function (Blueprint $table) {
            // 1. Agregar el campo para Microsoft GUID
            if (!Schema::hasColumn('md_auth_users', 'guid_ms')) {
                $table->uuid('guid_ms')->nullable()->index();
            }

            // 2. Corregir la Primary Key para permitir usuarios globales (tenant_id NULL)
            // Primero borramos la PK actual compuesta
            $table->dropPrimary(['tenant_id', 'user_id']);
            
            // Definimos user_id como la única PK (un usuario solo existe una vez en la tabla)
            $table->primary('user_id');
            
            // Agregamos índice para búsquedas por tenant si lo necesitas
            $table->index(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('md_auth_users', function (Blueprint $table) {
            $table->dropColumn('guid_ms');
            $table->dropPrimary();
            // Restaurar PK anterior (solo funcionará si no hay NULLs)
            $table->primary(['tenant_id', 'user_id']);
        });
    }
};
