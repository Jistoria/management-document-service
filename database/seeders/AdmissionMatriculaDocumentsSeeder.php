<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdmissionMatriculaDocumentsSeeder extends Seeder
{
    private const PREFIX = 'P';      // Prefijo institucional
    private const CAT_CODE = 'A';    // Admisión
    private const PROC_CODE = 'M';   // Matrícula (dentro de Admisión)

    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        // ------------------------------------------------------------------------------
        // 0) Asegurar Sede, Facultad y carreras (opcional si ya lo sembraste antes)
        //    Puedes comentar esta sección si ya se ejecutó en producción.
        // ------------------------------------------------------------------------------
        $headOfficeId = DB::table('head_offices')->where('code', 'ULEAM-MAN')->value('id')
            ?: (string) Str::uuid7();

        DB::table('head_offices')->updateOrInsert(
            ['code' => 'ULEAM-MAN'],
            [
                'id'         => $headOfficeId,
                'name'       => 'ULEAM Extensión Manta',
                'code'       => 'ULEAM-MAN',
                'created_at' => $now, 'updated_at' => $now,
                'version'    => 1, 'created_by' => 'system', 'updated_by' => 'system',
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
                'created_by' => 'system', 'updated_by' => 'system',
            ]
        );

        // ------------------------------------------------------------------------------
        // 1) Subsystem: DOCENCIA (code = 'A' según tu convención)
        // ------------------------------------------------------------------------------
        $subsystemId = DB::table('subsystems')->where('code', 'A')->value('id') ?: (string) Str::uuid7();
        DB::table('subsystems')->updateOrInsert(
            ['code' => 'A'],
            [
                'id'         => $subsystemId,
                'name'       => 'Docencia',
                'code'       => 'A',
                'created_at' => $now, 'updated_at' => $now,
                'created_by' => 'system', 'updated_by' => 'system',
            ]
        );

        // ------------------------------------------------------------------------------
        // 2) Category: ADMISIÓN (code = 'A')  → base "PA?"
        // ------------------------------------------------------------------------------
        $categoryId = (string) Str::uuid7();

        DB::table('process_categories')->insert([
            'id'           => $categoryId,
            'subsystem_id' => $subsystemId,
            'name'         => 'ADMISIÓN',
            'code'         => self::CAT_CODE,
            'created_at'   => $now,
            'updated_at'   => $now,
            'created_by'   => 'system',
            'updated_by'   => 'system',
        ]);

        // ------------------------------------------------------------------------------
        // 3) Process: MATRÍCULA (code = 'M') → raíz "PAM"
        // ------------------------------------------------------------------------------
        $processId = (string) Str::uuid7();

        DB::table('processes')->insert([
            'id'                  => $processId,
            'process_category_id' => $categoryId,
            'parent_id'           => null,
            'name'                => 'MATRÍCULA',
            'code'                => self::PROC_CODE, // 'M'
            'created_at'          => $now,
            'updated_at'          => $now,
            'created_by'          => 'system',
            'updated_by'          => 'system',
        ]);

        // ------------------------------------------------------------------------------
        // 4) Subproceso contenedor: PAM-04  (MATRÍCULAS ORDINARIAS Y EXTRAORDINARIAS)
        // ------------------------------------------------------------------------------
        $base3 = self::PREFIX . self::CAT_CODE . self::PROC_CODE; // "PAM"
        $subprocCode = $base3 . '-04';
        $subprocId = (string) Str::uuid7();

        DB::table('processes')->insert([
            'id'                  => $subprocId,
            'process_category_id' => $categoryId,
            'parent_id'           => $processId,
            'name'                => 'MATRÍCULAS ORDINARIAS Y EXTRAORDINARIAS',
            'code'                => $subprocCode, // PAM-04
            'created_at'          => $now,
            'updated_at'          => $now,
            'created_by'          => 'system',
            'updated_by'          => 'system',
        ]);

        // ------------------------------------------------------------------------------
        // 5) DOCUMENTOS (exactamente los 7 que enviaste)
        //     Guardamos: name y code_default (PAM-04-###)
        // ------------------------------------------------------------------------------
        $docs = [
            ['n' => 1, 'name' => 'Solicitud tercera matrícula'],
            ['n' => 2, 'name' => 'Acta de compromiso tercera matrícula'],
            ['n' => 3, 'name' => 'Solicitud de matrícula especial'],
            ['n' => 4, 'name' => 'Solicitud exoneración costo matrícula'],
            ['n' => 5, 'name' => 'Solicitud de retiro de asignaturas por causa fortuita o fuerza mayor'],
            ['n' => 6, 'name' => 'Solicitud matrícula excepcional'],
            ['n' => 7, 'name' => 'Solicitud para retiro de asignatura aplicabilidad de la resolución RPC-SE-03-No. 046-2020 CES'],
        ];

        foreach ($docs as $i => $d) {
            $codeDefault = sprintf('%s-%03d', $subprocCode, $d['n']); // PAM-04-001, ...
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
    }
}
