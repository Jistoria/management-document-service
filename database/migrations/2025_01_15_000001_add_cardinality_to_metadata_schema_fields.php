<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('metadata_schema_fields', function (Blueprint $table) {
            $table->boolean('is_repeatable')->default(false)->after('metadata_field_id');
            $table->unsignedInteger('min_occurs')->default(0)->after('is_repeatable');
            $table->unsignedInteger('max_occurs')->nullable()->after('min_occurs');
            $table->boolean('allow_duplicates')->default(true)->after('max_occurs');
        });

        DB::statement(
            'ALTER TABLE metadata_schema_fields '
            . 'ADD CONSTRAINT chk_metadata_schema_fields_occurs_valid '
            . 'CHECK (max_occurs IS NULL OR max_occurs >= min_occurs)'
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE metadata_schema_fields DROP CONSTRAINT IF EXISTS chk_metadata_schema_fields_occurs_valid');

        Schema::table('metadata_schema_fields', function (Blueprint $table) {
            $table->dropColumn(['is_repeatable', 'min_occurs', 'max_occurs', 'allow_duplicates']);
        });
    }
};
