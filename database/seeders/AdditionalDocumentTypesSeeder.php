<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Seeder to register additional document types used across
 * academic and vinculación processes.
 *
 * Based on the discussion in this conversation, there are a variety of
 * documents such as informes (reports), planning documents, activity
 * logbooks, evaluation reports, and requests/solicitudes.  These
 * high‑level categories do not exist in the initial database seed
 * (which only creates document types for academic and administrative
 * documents, certificates, and forms).  To properly classify the
 * required documents introduced in Vinculación and other subsystems,
 * this seeder inserts a set of additional document types if they
 * aren’t already present.  Each entry is keyed by a short code and
 * includes a human‑readable name and description.
 */
class AdditionalDocumentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        // Define the document types to insert.  If a code already exists,
        // the record will not be duplicated.  Codes should be short,
        // unique identifiers (≤5 characters) that describe the type.
        $documentTypes = [
            [
                'code'        => 'INF',
                'name'        => 'Informe',
                'description' => 'Informes y reportes de carácter mensual, semestral, socialización, técnicos, de evaluación y otros.',
            ],
            [
                'code'        => 'PLAN',
                'name'        => 'Planificación',
                'description' => 'Documentos de planificación y programación de actividades o proyectos.',
            ],
            [
                'code'        => 'REG',
                'name'        => 'Registro',
                'description' => 'Registros o bitácoras de actividades diarias, avances y otros controles.',
            ],
            [
                'code'        => 'EVAL',
                'name'        => 'Evaluación',
                'description' => 'Documentos de evaluación, resultados y retroalimentación de procesos y proyectos.',
            ],
            [
                'code'        => 'SOL',
                'name'        => 'Solicitud',
                'description' => 'Solicitudes, peticiones o formularios de requerimiento para iniciar actividades o procesos.',
            ],
        ];

        foreach ($documentTypes as $type) {
            // Check if a document type with the same code already exists.
            $exists = DB::table('document_types')
                ->where('code', $type['code'])
                ->whereNull('deleted_at')
                ->exists();

            if (!$exists) {
                DB::table('document_types')->insert([
                    'id'          => (string) Str::uuid7(),
                    'name'        => $type['name'],
                    'code'        => $type['code'],
                    'description' => $type['description'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                    'created_by'  => 'system',
                    'updated_by'  => 'system',
                    'version'     => 1,
                ]);
            }
        }
    }
}
