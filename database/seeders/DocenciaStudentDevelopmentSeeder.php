<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DocenciaStudentDevelopmentSeeder extends Seeder
{
    private const PREFIX = 'P';  // Prefijo institucional
    private const CAT    = 'A';  // Categoría (Gestión y Desarrollo Estudiantil)
    private const PROC_P = 'P';  // Prácticas preprofesionales y pasantías
    private const PROC_J = 'J';  // Ayudantía de cátedra e investigación
    private const PROC_I = 'I';  // Movilidad académica / convenios

    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        // ============================================================
        // 1) Obtener SUBSISTEMA DOCENCIA (code = 'A')
        //    Debe existir por BaseInstitutionSeeder
        // ============================================================
        $subsystemId = DB::table('subsystems')->where('code', 'A')->value('id');

        if (!$subsystemId) {
            throw new \RuntimeException('Subsystem DOCENCIA (code=A) no encontrado. Ejecuta BaseInstitutionSeeder primero.');
        }

        // ============================================================
        // 2) CATEGORÍA: GESTIÓN Y DESARROLLO ESTUDIANTIL (code = 'A')
        //    Verificar si ya existe para evitar duplicados
        // ============================================================
        $existingCategory = DB::table('process_categories')
            ->where('subsystem_id', $subsystemId)
            ->where('name', 'GESTIÓN Y DESARROLLO ESTUDIANTIL')
            ->where('code', self::CAT)
            ->first();

        if ($existingCategory) {
            $categoryId = $existingCategory->id;
        } else {
            $categoryId = (string) Str::uuid7();

            DB::table('process_categories')->insert([
                'id'               => $categoryId,
                'subsystem_id'     => $subsystemId,
                'name'             => 'GESTIÓN Y DESARROLLO ESTUDIANTIL',
                'code'             => self::CAT,
                'created_at'       => $now,
                'updated_at'       => $now,
                'created_by'       => 'system',
                'updated_by'       => 'system',
            ]);
        }

        // ============================================================
        // 3) PROCESO: PRÁCTICAS PREPROFESIONALES Y PASANTÍAS (P)
        //    con subproceso PAP-01 + documentos (lo que ya teníamos)
        // ============================================================
        $existingProcPract = DB::table('processes')
            ->where('process_category_id', $categoryId)
            ->where('code', self::PROC_P)
            ->whereNull('parent_id')
            ->first();

        if ($existingProcPract) {
            $procPractId = $existingProcPract->id;
        } else {
            $procPractId = (string) Str::uuid7();

            DB::table('processes')->insert([
                'id'                  => $procPractId,
                'process_category_id' => $categoryId,
                'parent_id'           => null,
                'name'                => 'PRÁCTICAS PREPROFESIONALES Y PASANTÍAS',
                'code'                => self::PROC_P,  // 'P'
                'created_at'          => $now,
                'updated_at'          => $now,
                'created_by'          => 'system',
                'updated_by'          => 'system',
            ]);
        }

        // --- Subproceso PAP-01 (planificación, ejecución, supervisión, evaluación)
        $base3       = self::PREFIX . self::CAT . self::PROC_P; // "PAP"
        $subprocCode = $base3 . '-01';                          // "PAP-01"

        $existingSubproc = DB::table('processes')
            ->where('process_category_id', $categoryId)
            ->where('parent_id', $procPractId)
            ->where('code', $subprocCode)
            ->first();

        if ($existingSubproc) {
            $subprocId = $existingSubproc->id;
        } else {
            $subprocId = (string) Str::uuid7();

            DB::table('processes')->insert([
                'id'                  => $subprocId,
                'process_category_id' => $categoryId,
                'parent_id'           => $procPractId,
                'name'                => 'PLANIFICACIÓN, EJECUCIÓN, SUPERVISIÓN Y EVALUACIÓN DE PRÁCTICAS PREPROFESIONALES Y PASANTÍAS',
                'code'                => $subprocCode, // PAP-01
                'created_at'          => $now,
                'updated_at'          => $now,
                'created_by'          => 'system',
                'updated_by'          => 'system',
            ]);
        }

        // --- Documentos PAP-01-F-001 ... PAP-01-F-006  =>  PAP-01-001 ... PAP-01-006
        $docs = [
            ['n' => 1, 'name' => 'Planificación Semestral de Prácticas Preprofesionales', 'type_code' => 'PLAN'],
            ['n' => 2, 'name' => 'Registro Actividades Diarias del Estudiante', 'type_code' => 'REG'],
            ['n' => 3, 'name' => 'Ficha para Supervisar al Estudiante', 'type_code' => 'EVAL'],
            ['n' => 4, 'name' => 'Informe Final del Estudiante', 'type_code' => 'INF'],
            ['n' => 5, 'name' => 'Evaluación General de Prácticas y Pasantías', 'type_code' => 'EVAL'],
            ['n' => 6, 'name' => 'Solicitud de prácticas preprofesionales', 'type_code' => 'SOL'],
        ];

        // Obtener los IDs de los tipos de documentos
        $docTypeIds = DB::table('document_types')
            ->whereIn('code', ['PLAN', 'REG', 'EVAL', 'INF', 'SOL'])
            ->pluck('id', 'code');

        foreach ($docs as $d) {
            $codeDefault = sprintf('%s-%03d', $subprocCode, $d['n']); // PAP-01-001, ...
            $documentTypeId = $docTypeIds[$d['type_code']] ?? null;

            if (!$documentTypeId) {
                throw new \RuntimeException("Tipo de documento '{$d['type_code']}' no encontrado. Ejecuta AdditionalDocumentTypesSeeder primero.");
            }

            // Solo insertar si no existe
            $existingDoc = DB::table('required_documents')
                ->where('process_id', $subprocId)
                ->where('code_default', $codeDefault)
                ->exists();

            if (!$existingDoc) {
                DB::table('required_documents')->insert([
                    'id'               => (string) Str::uuid7(),
                    'process_id'       => $subprocId,
                    'document_type_id' => $documentTypeId,
                    'name'             => $d['name'],
                    'code_default'     => $codeDefault,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                    'created_by'       => 'system',
                    'updated_by'       => 'system',
                ]);
            }
        }

        // ============================================================
        // 4) PROCESO: AYUDANTÍA DE CÁTEDRA E INVESTIGACIÓN (J)
        //    SOLO proceso padre, sin hijos (sin subprocesos ni documentos)
        // ============================================================
        $existingProcAyud = DB::table('processes')
            ->where('process_category_id', $categoryId)
            ->where('code', self::PROC_J)
            ->whereNull('parent_id')
            ->exists();

        if (!$existingProcAyud) {
            DB::table('processes')->insert([
                'id'                  => (string) Str::uuid7(),
                'process_category_id' => $categoryId,
                'parent_id'           => null,
                'name'                => 'AYUDANTÍA DE CÁTEDRA E INVESTIGACIÓN',
                'code'                => self::PROC_J,  // 'J'
                'created_at'          => $now,
                'updated_at'          => $now,
                'created_by'          => 'system',
                'updated_by'          => 'system',
            ]);
        }

        // ============================================================
        // 5) PROCESO: MOVILIDAD ACADÉMICA / CONVENIOS (I)
        //    SOLO proceso padre, sin hijos
        // ============================================================
        $existingProcMov = DB::table('processes')
            ->where('process_category_id', $categoryId)
            ->where('code', self::PROC_I)
            ->whereNull('parent_id')
            ->exists();

        if (!$existingProcMov) {
            DB::table('processes')->insert([
                'id'                  => (string) Str::uuid7(),
                'process_category_id' => $categoryId,
                'parent_id'           => null,
                'name'                => 'MOVILIDAD ACADÉMICA / CONVENIOS',
                'code'                => self::PROC_I,  // 'I'
                'created_at'          => $now,
                'updated_at'          => $now,
                'created_by'          => 'system',
                'updated_by'          => 'system',
            ]);
        }

        // (SEGUIMIENTO A GRADUADOS de momento no se crea,
        //  porque en el catálogo no tiene código ni manual definido.)
    }
}
