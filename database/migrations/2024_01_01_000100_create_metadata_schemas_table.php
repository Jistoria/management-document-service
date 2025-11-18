<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('metadata_schemas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_canonical')->default(false);
            $table->unsignedInteger('version')->default(1);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metadata_schemas');
    }
};
