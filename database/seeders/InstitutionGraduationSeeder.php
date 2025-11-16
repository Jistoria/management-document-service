<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Seeder de producción alineado al Catálogo institucional:
 *  - Prefijo institucional: 'P'
 *  - Código proceso 3 letras = 'P' + category.code (1) + process.code (1)
 *  - Subproceso = <P??> + '-NNN'
 *  - Documentos requeridos (opcional) = <P??-NNN> + '-NNN'
 */
class InstitutionGraduationSeeder extends Seeder
{
    private const PREFIX = 'P';

    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        // =========================================================================
        // 1) ORGANIZACIÓN: Sede, Facultad (Departamento) y Carreras vigentes
        // =========================================================================
        $headOfficeId = DB::table('head_offices')->where('code', 'ULEAM-MAN')->value('id') ?: (string) Str::uuid7();
        DB::table('head_offices')->updateOrInsert(
            ['code' => 'ULEAM-MAN'],
            [
                'id'           => $headOfficeId,
                'name'         => 'ULEAM Extensión Manta',
                'code'         => 'ULEAM-MAN',
                'code_numeric' => null,
                'created_at'   => $now, 'updated_at' => $now,
                'version'      => 1, 'created_by' => 'system', 'updated_by' => 'system',
            ]
        );

        $deptId = DB::table('departments')
            ->where('head_office_id', $headOfficeId)
            ->where('code', 'FCVT')->value('id') ?: (string) Str::uuid7();

        DB::table('departments')->updateOrInsert(
            ['id' => $deptId],
            [
                'id'             => $deptId,
                'head_office_id' => $headOfficeId,
                'name'           => 'Facultad de Ciencias de la Vida y Tecnologías',
                'code'           => 'FCVT',
                'code_numeric'   => '213',
                'created_at'     => $now, 'updated_at' => $now,
                'version'        => 1, 'created_by' => 'system', 'updated_by' => 'system',
            ]
        );

        $careers = [
            ['name' => 'Agropecuaria',                   'code_numeric' => '213.1'],
            ['name' => 'Ingeniería en Agropecuaria',     'code_numeric' => '213.2'],
            ['name' => 'Agronegocios',                   'code_numeric' => '213.4'],
            ['name' => 'Agroindustria',                  'code_numeric' => '213.5'],
            ['name' => 'Ingeniería Ambiental',           'code_numeric' => '213.7'],
            ['name' => 'Tecnologías de la Información',  'code_numeric' => '213.9'],
            ['name' => 'Software',                       'code_numeric' => '213.11'],
            ['name' => 'Biología',                       'code_numeric' => '213.14'],
            ['name' => 'Alimentos',                      'code_numeric' => '213.17'],
        ];
        foreach ($careers as $c) {
            DB::table('careers')->updateOrInsert(
                ['department_id' => $deptId, 'code_numeric' => $c['code_numeric']],
                [
                    'id'            => (string) Str::uuid7(),
                    'department_id' => $deptId,
                    'name'          => $c['name'],
                    'code'          => Str::of($c['name'])->upper()->slug('_'),
                    'code_numeric'  => $c['code_numeric'],
                    'created_at'    => $now, 'updated_at' => $now,
                    'version'       => 1, 'created_by' => 'system', 'updated_by' => 'system',
                ]
            );
        }

        // =========================================================================
        // 2) DOCENCIA (Subsistema)  ->  GRADUACIÓN (Categoría)  ->  TITULACIÓN (Proceso)
        // =========================================================================
        // Subsistema Docencia (tu convención: code = 'A')
        $subsystemId = DB::table('subsystems')->where('code', 'A')->value('id') ?: (string) Str::uuid7();
        DB::table('subsystems')->updateOrInsert(
            ['code' => 'A'],
            [
                'id'           => $subsystemId,
                'name'         => 'Docencia',
                'code'         => 'A', // la letra que participa en el P??
                'code_numeric' => null,
                'created_at'   => $now, 'updated_at' => $now,
                'version'      => 1, 'created_by' => 'system', 'updated_by' => 'system',
            ]
        );

        // Categoría: GRADUACIÓN (code = 'A' según tu regla)
        $categoryId = DB::table('process_categories')
            ->where('subsystem_id', $subsystemId)->where('code', 'A')->value('id') ?: (string) Str::uuid7();

        DB::table('process_categories')->updateOrInsert(
            ['id' => $categoryId],
            [
                'id'               => $categoryId,
                'subsystem_id'     => $subsystemId,
                'name'             => 'GRADUACIÓN',
                'code'             => 'A',
                'numeric_code'     => null,
                'created_at'       => $now, 'updated_at' => $now,
                'version'          => 1, 'created_by' => 'system', 'updated_by' => 'system',
            ]
        );

        // Proceso: TITULACIÓN (code = 'T')
        $processId = DB::table('processes')
            ->where('process_category_id', $categoryId)
            ->whereNull('parent_id')
            ->where('code', 'T')->value('id') ?: (string) Str::uuid7();

        DB::table('processes')->updateOrInsert(
            ['id' => $processId],
            [
                'id'                  => $processId,
                'process_category_id' => $categoryId,
                'parent_id'           => null,
                'name'                => 'TITULACIÓN',
                'code'                => 'T', // solo la letra de proceso
                'numeric_code'        => null,
                'created_at'          => $now, 'updated_at' => $now,
                'version'             => 1, 'created_by' => 'system', 'updated_by' => 'system',
            ]
        );

        // Helper: genera el “P??” de 3 letras para este árbol
        $base3 = self::PREFIX . 'A' . 'T'; // P + category(A) + process(T)  => "PAT"

        // =========================================================================
        // 3) SUBPROCESOS de Titulación (códigos PAT-001, PAT-002, …)
        //    Puedes ampliar la lista según el catálogo (PAT-01, PAT-02, PAT-04, PAT-05-IT-001, etc.)
        // =========================================================================
        $subprocesses = [
            ['n' => 1, 'name' => 'Titulación de estudiantes de grado'],
            ['n' => 2, 'name' => 'Emisión y registro de título de grado y postgrado'],
            ['n' => 3, 'name' => 'Titulación: UIC y Unidad de Titulación'],
            ['n' => 4, 'name' => 'Titulación de carreras técnicas y tecnológicas'],
        ];

        foreach ($subprocesses as $i => $sp) {
            $code = sprintf('%s-%03d', $base3, $sp['n']); // PAT-001
            DB::table('processes')->updateOrInsert(
                [
                    'process_category_id' => $categoryId,
                    'parent_id'           => $processId,
                    'code'                => $code,
                ],
                [
                    'id'                  => (string) Str::uuid7(),
                    'process_category_id' => $categoryId,
                    'parent_id'           => $processId,
                    'name'                => strtoupper($sp['name']),
                    'code'                => $code,
                    'numeric_code'        => null,
                    'created_at'          => $now, 'updated_at' => $now,
                    'version'             => 1, 'created_by' => 'system', 'updated_by' => 'system',
                ]
            );
        }

        // =========================================================================
        // 4) (OPCIONAL) DOCUMENTOS REQUERIDOS con code_default heredado PAT-001-001, …
        //     Descomenta si existe la tabla `required_documents` como:
        //     id uuid, process_id uuid, name varchar, code_default varchar, created_at, updated_at, version, created_by, updated_by
        // =========================================================================
        /*
        $subIds = DB::table('processes')
            ->where('process_category_id', $categoryId)
            ->where('parent_id', $processId)
            ->pluck('id', 'code'); // ['PAT-001' => <uuid>, ...]

        $docs = [
            ['spCode' => 'PAT-001', 'n' => 1, 'name' => 'Solicitud de acto de grado'],
            ['spCode' => 'PAT-001', 'n' => 2, 'name' => 'Acta de grado firmada'],
            ['spCode' => 'PAT-002', 'n' => 1, 'name' => 'Resolución de aprobación de título'],
            ['spCode' => 'PAT-003', 'n' => 1, 'name' => 'Certificación UIC'],
            ['spCode' => 'PAT-004', 'n' => 1, 'name' => 'Informe de requisitos de tecnólogos'],
        ];

        foreach ($docs as $d) {
            $codeDefault = sprintf('%s-%03d', $d['spCode'], $d['n']); // PAT-001-001
            DB::table('required_documents')->updateOrInsert(
                ['process_id' => $subIds[$d['spCode']], 'code_default' => $codeDefault],
                [
                    'id'           => (string) Str::uuid(),
                    'process_id'   => $subIds[$d['spCode']],
                    'name'         => $d['name'],
                    'code_default' => $codeDefault,
                    'created_at'   => $now, 'updated_at' => $now,
                    'version'      => 1, 'created_by' => 'system', 'updated_by' => 'system',
                ]
            );
        }
        */
    }
}
