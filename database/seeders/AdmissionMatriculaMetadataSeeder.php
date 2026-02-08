<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdmissionMatriculaMetadataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        // Obtener el proceso PAM-04
        $process = DB::table('processes')
            ->where('code', 'PAM-04')
            ->first();

        if (!$process) {
            $this->command->error('❌ Proceso PAM-04 no encontrado');
            return;
        }

        // Crear esquema de metadata para documentos de admisión y matrícula
        $schemaId = (string) Str::uuid7();
        DB::table('metadata_schemas')->insert([
            'id' => $schemaId,
            'name' => 'PAM-04 Admisión y Matrícula - Esquema General',
            'description' => 'Metadatos generales aplicables a todos los documentos del proceso PAM-04 (Admisión y Matrícula)',
            'version' => 1,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => 'system',
            'updated_by' => 'system',
        ]);

        // Obtener campos de metadata universitarios comunes con validaciones
        $fieldsConfig = [
            'student_id' => [
                'order' => 1,
                'required' => true,
                'regex' => '^\d{10}$',
                'error_msg' => 'La cédula debe contener exactamente 10 dígitos numéricos.',
            ],
            'student_name' => [
                'order' => 2,
                'required' => false,
                'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.]+$',
                'error_msg' => 'Solo se permiten letras y espacios.',
            ],
            'career' => [
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
            'document_number' => [
                'order' => 6,
                'required' => false,
                'regex' => '^\d+$',
                'error_msg' => 'Debe ser un número entero positivo.',
            ],
            'academic_unit' => [
                'order' => 7,
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

        // Asignar el esquema a todos los documentos PAM-04 sin schema
        DB::table('required_documents')
            ->where('process_id', $process->id)
            ->whereNull('metadata_schema_id')
            ->update([
                'metadata_schema_id' => $schemaId,
                'updated_at' => $now,
                'updated_by' => 'system',
            ]);

        $this->command->info('✅ Esquema de metadata PAM-04 creado y asignado a 7 documentos');
    }
}
