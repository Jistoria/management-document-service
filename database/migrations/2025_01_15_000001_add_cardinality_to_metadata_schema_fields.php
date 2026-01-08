<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si las columnas ya existen antes de agregarlas
        Schema::table('metadata_schema_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('metadata_schema_fields', 'is_repeatable')) {
                $table->boolean('is_repeatable')->default(false)->after('metadata_field_id');
            }
            if (!Schema::hasColumn('metadata_schema_fields', 'min_occurs')) {
                $table->unsignedInteger('min_occurs')->default(0)->after('is_repeatable');
            }
            if (!Schema::hasColumn('metadata_schema_fields', 'max_occurs')) {
                $table->unsignedInteger('max_occurs')->nullable()->after('min_occurs');
            }
            if (!Schema::hasColumn('metadata_schema_fields', 'allow_duplicates')) {
                $table->boolean('allow_duplicates')->default(true)->after('max_occurs');
            }
        });

        // Agregar constraint solo si no existe
        $constraintExists = DB::select(
            "SELECT constraint_name FROM information_schema.table_constraints 
             WHERE table_name = 'metadata_schema_fields' 
             AND constraint_name = 'chk_metadata_schema_fields_occurs_valid'"
        );

        if (empty($constraintExists)) {
            DB::statement(
                'ALTER TABLE metadata_schema_fields '
                . 'ADD CONSTRAINT chk_metadata_schema_fields_occurs_valid '
                . 'CHECK (max_occurs IS NULL OR max_occurs >= min_occurs)'
            );
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE metadata_schema_fields DROP CONSTRAINT IF EXISTS chk_metadata_schema_fields_occurs_valid');

        Schema::table('metadata_schema_fields', function (Blueprint $table) {
            $table->dropColumn(['is_repeatable', 'min_occurs', 'max_occurs', 'allow_duplicates']);
        });
    }
};
