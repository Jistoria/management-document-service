<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('md_auth_users', function (Blueprint $table) {
            // 1. Agregar el campo para Microsoft GUID solo si no existe
            if (!Schema::hasColumn('md_auth_users', 'guid_ms')) {
                $table->uuid('guid_ms')->nullable()->index();
            }
        });

        // 2. Verificar y corregir la Primary Key
        $primaryKeyColumns = DB::select(
            "SELECT string_agg(a.attname, ', ') as columns
             FROM pg_index i
             JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey)
             WHERE i.indrelid = 'md_auth_users'::regclass AND i.indisprimary"
        );

        $currentPK = $primaryKeyColumns[0]->columns ?? '';

        // Si la PK actual es compuesta (tenant_id, user_id), la cambiamos
        if (str_contains($currentPK, 'tenant_id') && str_contains($currentPK, 'user_id')) {
            DB::statement('ALTER TABLE md_auth_users DROP CONSTRAINT md_auth_users_pkey');
            DB::statement('ALTER TABLE md_auth_users ADD PRIMARY KEY (user_id)');

            // Agregar índice para búsquedas por tenant si no existe
            $indexExists = DB::select(
                "SELECT indexname FROM pg_indexes
                 WHERE tablename = 'md_auth_users'
                 AND indexname = 'md_auth_users_tenant_id_user_id_index'"
            );

            if (empty($indexExists)) {
                DB::statement('CREATE INDEX md_auth_users_tenant_id_user_id_index ON md_auth_users (tenant_id, user_id)');
            }
        }

        Schema::table('required_documents', function (Blueprint $table) {
            // Ruta del archivo en MinIO
            $table->string('template_path')->nullable()->after('description');
            // Nombre original para mostrar en el botón de descarga (ej: "Formato_Tesis_v2.docx")
            $table->string('template_filename')->nullable()->after('template_path');
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
