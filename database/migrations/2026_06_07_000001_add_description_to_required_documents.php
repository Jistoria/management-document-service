<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La columna `name` ya existe en required_documents (text NOT NULL, default 'S/N').
 * Acá solo agregamos `description`, que el RequiredDocumentResource ya expone
 * pero que no tenía columna (devolvía null).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('required_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('required_documents', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('required_documents', function (Blueprint $table) {
            if (Schema::hasColumn('required_documents', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
