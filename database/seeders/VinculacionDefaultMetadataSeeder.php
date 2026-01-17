<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VinculacionDefaultMetadataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        // Obtener el proceso TCT-02 (Vinculación)
        $process = DB::table('processes')
            ->where('code', 'TCT-02')
            ->first();

        if (!$process) {
            $this->command->error('❌ Proceso TCT-02 no encontrado');
            return;
        }

        // Crear esquema de metadata general para documentos de vinculación
        $schemaId = (string) Str::uuid7();
        DB::table('metadata_schemas')->insert([
            'id' => $schemaId,
            'name' => 'TCT-02 Vinculación con la Sociedad - Esquema General',
            'description' => 'Metadatos generales aplicables a documentos del proceso TCT-02 (Vinculación con la Sociedad)',
            'version' => 1,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => 'system',
            'updated_by' => 'system',
        ]);

        // Obtener campos de metadata universitarios comunes para vinculación
        $fieldsMap = [
            'project_name' => 1,
            'project_code' => 2,
            'author' => 3,
            'academic_period' => 4,
            'document_date' => 5,
            'career' => 6,
            'department' => 7,
            'beneficiary_entity' => 8,
        ];

        // Asignar campos al esquema
        foreach ($fieldsMap as $fieldCode => $order) {
            $fieldId = DB::table('metadata_fields')
                ->where('field_key', $fieldCode)
                ->value('id');

            if ($fieldId) {
                DB::table('metadata_schema_fields')->insert([
                    'id' => (string) Str::uuid7(),
                    'metadata_schema_id' => $schemaId,
                    'metadata_field_id' => $fieldId,
                    'is_required' => in_array($fieldCode, ['project_name', 'author', 'academic_period']),
                    'sort_order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'created_by' => 'system',
                    'updated_by' => 'system',
                ]);
            }
        }

        // Asignar el esquema solo a documentos TCT-02 que NO tienen schema
        DB::table('required_documents')
            ->where('process_id', $process->id)
            ->whereNull('metadata_schema_id')
            ->update([
                'metadata_schema_id' => $schemaId,
                'updated_at' => $now,
                'updated_by' => 'system',
            ]);

        $documentsUpdated = DB::table('required_documents')
            ->where('process_id', $process->id)
            ->where('metadata_schema_id', $schemaId)
            ->count();

        $this->command->info("✅ Esquema de metadata TCT-02 creado y asignado a {$documentsUpdated} documentos");
    }
}
