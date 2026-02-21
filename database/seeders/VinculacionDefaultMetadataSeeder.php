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
        $fieldsConfig = [
            'project_name' => [
                'order' => 1,
                'required' => true,
                'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.]+$',
                'error_msg' => 'Solo se permiten letras y espacios.',
            ],
            'project_code' => [
                'order' => 2,
                'required' => false,
                'regex' => '^[A-Z0-9\-]+$',
                'error_msg' => 'Solo se permiten letras mayúsculas, números y guiones.',
            ],
            'author' => [
                'order' => 3,
                'required' => true,
                'regex' => null,
                'error_msg' => null,
            ],
            'academic_period' => [
                'order' => 4,
                'required' => true,
                'regex' => '^\d{4}-\d{1}$',
                'error_msg' => 'El formato debe ser AAAA-P (ej. 2025-1).',
            ],
            'document_date' => [
                'order' => 5,
                'required' => false,
                'regex' => null,
                'error_msg' => null,
            ],
            'career' => [
                'order' => 6,
                'required' => false,
                'regex' => null,
                'error_msg' => null,
            ],
            'department' => [
                'order' => 7,
                'required' => false,
                'regex' => null,
                'error_msg' => null,
            ],
            'beneficiary_entity' => [
                'order' => 8,
                'required' => false,
                'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.]+$',
                'error_msg' => 'Solo se permiten letras y espacios.',
            ],
        ];

        // Asignar campos al esquema con validaciones
        foreach ($fieldsConfig as $fieldCode => $config) {
            $fieldId = DB::table('metadata_fields')
                ->where('field_key', $fieldCode)
                ->value('id');

            if ($fieldId) {
                DB::table('metadata_schema_fields')->insert([
                    'id'                        => (string) Str::uuid7(),
                    'metadata_schema_id'        => $schemaId,
                    'metadata_field_id'         => $fieldId,
                    'is_required'               => $config['required'],
                    'sort_order'                => $config['order'],
                    'regex_pattern'             => $config['regex'],
                    'validation_error_message'  => $config['error_msg'],
                    'created_at'                => $now,
                    'updated_at'                => $now,
                    'created_by'                => 'system',
                    'updated_by'                => 'system',
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
