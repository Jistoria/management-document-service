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
        // 1) SUBSISTEMA DOCENCIA (code = 'A')
        // ============================================================
        $subsystemId = DB::table('subsystems')->where('code', 'A')->value('id') ?: (string) Str::uuid7();

        DB::table('subsystems')->updateOrInsert(
            ['code' => 'A'],
            [
                'id'         => $subsystemId,
                'name'       => 'Docencia',
                'code'       => 'A',
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 'system',
                'updated_by' => 'system',
            ]
        );

        // ============================================================
        // 2) CATEGORÍA: GESTIÓN Y DESARROLLO ESTUDIANTIL (code = 'A')
        // ============================================================
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

        // ============================================================
        // 3) PROCESO: PRÁCTICAS PREPROFESIONALES Y PASANTÍAS (P)
        //    con subproceso PAP-01 + documentos (lo que ya teníamos)
        // ============================================================
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

        // --- Subproceso PAP-01 (planificación, ejecución, supervisión, evaluación)
        $base3       = self::PREFIX . self::CAT . self::PROC_P; // "PAP"
        $subprocCode = $base3 . '-01';                          // "PAP-01"
        $subprocId   = (string) Str::uuid7();

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

        // --- Documentos PAP-01-F-001 ... PAP-01-F-006  =>  PAP-01-001 ... PAP-01-006
        $docs = [
            ['n' => 1, 'name' => 'Planificación Semestral de Prácticas Preprofesionales'],
            ['n' => 2, 'name' => 'Registro Actividades Diarias del Estudiante'],
            ['n' => 3, 'name' => 'Ficha para Supervisar al Estudiante'],
            ['n' => 4, 'name' => 'Informe Final del Estudiante'],
            ['n' => 5, 'name' => 'Evaluación General de Prácticas y Pasantías'],
            ['n' => 6, 'name' => 'Solicitud de prácticas preprofesionales'],
        ];

        foreach ($docs as $d) {
            $codeDefault = sprintf('%s-%03d', $subprocCode, $d['n']); // PAP-01-001, ...
            DB::table('required_documents')->insert([
                'id'           => (string) Str::uuid7(),
                'process_id'   => $subprocId,
                'name'         => $d['name'],
                'code_default' => $codeDefault,
                'created_at'   => $now,
                'updated_at'   => $now,
                'created_by'   => 'system',
                'updated_by'   => 'system',
            ]);
        }

        // ============================================================
        // 4) PROCESO: AYUDANTÍA DE CÁTEDRA E INVESTIGACIÓN (J)
        //    SOLO proceso padre, sin hijos (sin subprocesos ni documentos)
        // ============================================================
        $procAyudId = (string) Str::uuid7();

        DB::table('processes')->insert([
            'id'                  => $procAyudId,
            'process_category_id' => $categoryId,
            'parent_id'           => null,
            'name'                => 'AYUDANTÍA DE CÁTEDRA E INVESTIGACIÓN',
            'code'                => self::PROC_J,  // 'J'
            'created_at'          => $now,
            'updated_at'          => $now,
            'version'             => 1,
            'created_by'          => 'system',
            'updated_by'          => 'system',
        ]);

        // ============================================================
        // 5) PROCESO: MOVILIDAD ACADÉMICA / CONVENIOS (I)
        //    SOLO proceso padre, sin hijos
        // ============================================================
        $procMovId = (string) Str::uuid7();

        DB::table('processes')->insert([
            'id'                  => $procMovId,
            'process_category_id' => $categoryId,
            'parent_id'           => null,
            'name'                => 'MOVILIDAD ACADÉMICA / CONVENIOS',
            'code'                => self::PROC_I,  // 'I'
            'created_at'          => $now,
            'updated_at'          => $now,
            'version'             => 1,
            'created_by'          => 'system',
            'updated_by'          => 'system',
        ]);

        // (SEGUIMIENTO A GRADUADOS de momento no se crea,
        //  porque en el catálogo no tiene código ni manual definido.)
    }
}
