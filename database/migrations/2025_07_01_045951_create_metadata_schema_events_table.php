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
        Schema::create('metadata_schema_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('schema_id');
            $table->string('event_type');
            $table->uuid('actor_id')->nullable();
            $table->timestamp('event_time')->useCurrent();
            $table->jsonb('details')->nullable();

            $table->foreign('schema_id')->references('id')->on('metadata_schemas')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metadata_schema_events');
    }
};
