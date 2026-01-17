<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeder para categorías y procesos del subsistema de Vinculación con la Sociedad.
 *
 * Este seeder crea las categorías principales del subsistema "Vinculación con la
 * Sociedad" y, para la categoría "Gestión del Conocimiento", registra el proceso
 * principal "Transferencia de Conocimiento y Tecnología" junto con tres
 * subprocesos. Además, inserta los documentos requeridos para el subproceso
 * de ejecución y monitoreo (PVV‑02‑F‑001 … PVV‑02‑F‑007).
 */
class VinculacionGestionConocimientoSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        // -------------------------------------------------------------------------
        // 1) Obtener el ID del subsistema Vinculación con la Sociedad (code = 'V').
        //    Este subsistema debe existir por AllSubsystemsSeeder.
        // -------------------------------------------------------------------------
        $subsystemId = DB::table('subsystems')
            ->where('code', 'V')
            ->value('id');

        if (!$subsystemId) {
            throw new \RuntimeException('Subsistema "Vinculación con la Sociedad" (code=V) no encontrado. Ejecuta AllSubsystemsSeeder primero.');
        }

        // -------------------------------------------------------------------------
        // 2) Crear o recuperar las categorías del subsistema Vinculación.
        //    Se insertan las cuatro categorías principales. Solo la categoría
        //    "Gestión del Conocimiento" se poblará con procesos y documentos en
        //    este seeder.
        // -------------------------------------------------------------------------
        $categories = [
            ['code' => 'GC', 'name' => 'GESTIÓN DEL CONOCIMIENTO'],
            ['code' => 'EC', 'name' => 'EDUCACIÓN CONTINUA'],
            ['code' => 'CE', 'name' => 'COOPERACIÓN, DESARROLLO Y EMPRENDIMIENTO'],
            ['code' => 'RD', 'name' => 'REDES'],
        ];

        $categoryIds = [];
        foreach ($categories as $cat) {
            $existing = DB::table('process_categories')
                ->where('subsystem_id', $subsystemId)
                ->where('code', $cat['code'])
                ->first();

            if ($existing) {
                $categoryId = $existing->id;
            } else {
                $categoryId = (string) Str::uuid7();

                DB::table('process_categories')->insert([
                    'id'           => $categoryId,
                    'subsystem_id' => $subsystemId,
                    'name'         => $cat['name'],
                    'code'         => $cat['code'],
                    'created_at'   => $now,
                    'updated_at'   => $now,
                    'created_by'   => 'system',
                    'updated_by'   => 'system',
                ]);
            }

            $categoryIds[$cat['code']] = $categoryId;
        }

        // -------------------------------------------------------------------------
        // 3) Procesos de la categoría "Gestión del Conocimiento" (código GC).
        //    Se define un proceso raíz "Transferencia de Conocimiento y Tecnología"
        //    con código TCT, luego tres subprocesos (TCT‑01, TCT‑02 y TCT‑03).
        // -------------------------------------------------------------------------
        $gcCategoryId = $categoryIds['GC'];

        $existingProcess = DB::table('processes')
            ->where('process_category_id', $gcCategoryId)
            ->where('code', 'TCT')
            ->whereNull('parent_id')
            ->first();

        if ($existingProcess) {
            $processId = $existingProcess->id;
        } else {
            $processId = (string) Str::uuid7();

            DB::table('processes')->insert([
                'id'                  => $processId,
                'process_category_id' => $gcCategoryId,
                'parent_id'           => null,
                'name'                => 'TRANSFERENCIA DE CONOCIMIENTO Y TECNOLOGÍA',
                'code'                => 'TCT',
                'order'               => 0,
                'created_at'          => $now,
                'updated_at'          => $now,
                'created_by'          => 'system',
                'updated_by'          => 'system',
            ]);
        }

        // Definición de los tres subprocesos bajo TCT.
        $subProcesses = [
            ['code' => 'TCT-01', 'name' => 'PLANIFICACIÓN DE PROGRAMAS Y PROYECTOS DE VINCULACIÓN CON LA SOCIEDAD'],
            ['code' => 'TCT-02', 'name' => 'EJECUCIÓN, MONITOREO Y SEGUIMIENTO DE PROGRAMAS Y PROYECTOS DE VINCULACIÓN CON LA SOCIEDAD'],
            ['code' => 'TCT-03', 'name' => 'EVALUACIÓN DE LOGROS Y RESULTADOS DE PROYECTOS DE VINCULACIÓN CON LA SOCIEDAD'],
        ];

        $subProcIds = [];
        foreach ($subProcesses as $sp) {
            $existingSubproc = DB::table('processes')
                ->where('process_category_id', $gcCategoryId)
                ->where('parent_id', $processId)
                ->where('code', $sp['code'])
                ->first();

            if ($existingSubproc) {
                $subProcIds[$sp['code']] = $existingSubproc->id;
            } else {
                $subId = (string) Str::uuid7();

                DB::table('processes')->insert([
                    'id'                  => $subId,
                    'process_category_id' => $gcCategoryId,
                    'parent_id'           => $processId,
                    'name'                => $sp['name'],
                    'code'                => $sp['code'],
                    'order'               => 0,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                    'created_by'          => 'system',
                    'updated_by'          => 'system',
                ]);

                $subProcIds[$sp['code']] = $subId;
            }
        }

        // -------------------------------------------------------------------------
        // 4) Documentos requeridos para el subproceso de ejecución y monitoreo.
        //    Código del subproceso: TCT-02. Se registran los siete documentos
        //    definidos por el manual institucional de Vinculación con la Sociedad.
        // -------------------------------------------------------------------------
        $execSubprocId = $subProcIds['TCT-02'];

        // Todos los documentos de vinculación son informes (tipo INF)
        $infTypeId = DB::table('document_types')
            ->where('code', 'INF')
            ->value('id');

        if (!$infTypeId) {
            throw new \RuntimeException('Tipo de documento "INF" (Informe) no encontrado. Ejecuta AdditionalDocumentTypesSeeder primero.');
        }

        $documents = [
            ['code_default' => 'PVV-02-F-001', 'name' => 'Informe semestral del Responsable de Vinculación y emprendimiento'],
            ['code_default' => 'PVV-02-F-002', 'name' => 'Informe semestral del Líder del proyecto de Vinculación'],
            ['code_default' => 'PVV-02-F-003', 'name' => 'Informe mensual del Líder del proyecto de Vinculación'],
            ['code_default' => 'PVV-02-F-004', 'name' => 'Informe semestral del Supervisor del proyecto de Vinculación con la sociedad'],
            ['code_default' => 'PVV-02-F-005', 'name' => 'Informe mensual del Supervisor del proyecto de Vinculación con la sociedad'],
            ['code_default' => 'PVV-02-F-006', 'name' => 'Informe de Socialización de los resultados obtenidos en proyectos de vinculación con la sociedad'],
            ['code_default' => 'PVV-02-F-007', 'name' => 'Informe Técnico de cumplimiento de tareas del Estudiante'],
        ];

        $order = 1;
        foreach ($documents as $doc) {
            $existingDoc = DB::table('required_documents')
                ->where('process_id', $execSubprocId)
                ->where('code_default', $doc['code_default'])
                ->exists();

            if (!$existingDoc) {
                DB::table('required_documents')->insert([
                    'id'               => (string) Str::uuid7(),
                    'process_id'       => $execSubprocId,
                    'document_type_id' => $infTypeId,
                    'name'             => $doc['name'],
                    'code_default'     => $doc['code_default'],
                    'order'            => $order++,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                    'created_by'       => 'system',
                    'updated_by'       => 'system',
                ]);
            }
        }
    }
}