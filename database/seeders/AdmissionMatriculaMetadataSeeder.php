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

        // Obtener campos de metadata universitarios comunes
        $fieldsMap = [
            'student_id' => 1,
            'student_name' => 2,
            'career' => 3,
            'academic_period' => 4,
            'document_date' => 5,
            'document_number' => 6,
            'academic_unit' => 7,
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
                    'is_required' => in_array($fieldCode, ['student_id', 'career', 'academic_period']),
                    'sort_order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'created_by' => 'system',
                    'updated_by' => 'system',
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
