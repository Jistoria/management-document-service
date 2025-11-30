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
        // 1) Obtener Subsystem DOCENCIA (debe existir por BaseInstitutionSeeder)
        // ------------------------------------------------------------------------------
        $subsystemId = DB::table('subsystems')->where('code', 'A')->value('id');

        if (!$subsystemId) {
            throw new \RuntimeException('Subsystem DOCENCIA (code=A) no encontrado. Ejecuta BaseInstitutionSeeder primero.');
        }

        // ------------------------------------------------------------------------------
        // 2) Category: ADMISIÓN (code = 'A')  → base "PA?"
        //    Verificar si ya existe para evitar duplicados
        // ------------------------------------------------------------------------------
        $existingCategory = DB::table('process_categories')
            ->where('subsystem_id', $subsystemId)
            ->where('code', self::CAT_CODE)
            ->first();

        if ($existingCategory) {
            $categoryId = $existingCategory->id;
        } else {
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
        }

        // ------------------------------------------------------------------------------
        // 3) Process: MATRÍCULA (code = 'M') → raíz "PAM"
        //    Verificar si ya existe
        // ------------------------------------------------------------------------------
        $existingProcess = DB::table('processes')
            ->where('process_category_id', $categoryId)
            ->where('code', self::PROC_CODE)
            ->whereNull('parent_id')
            ->first();

        if ($existingProcess) {
            $processId = $existingProcess->id;
        } else {
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
        }

        // ------------------------------------------------------------------------------
        // 4) Subproceso contenedor: PAM-04  (MATRÍCULAS ORDINARIAS Y EXTRAORDINARIAS)
        //    Verificar si ya existe
        // ------------------------------------------------------------------------------
        $base3 = self::PREFIX . self::CAT_CODE . self::PROC_CODE; // "PAM"
        $subprocCode = $base3 . '-04';

        $existingSubproc = DB::table('processes')
            ->where('process_category_id', $categoryId)
            ->where('parent_id', $processId)
            ->where('code', $subprocCode)
            ->first();

        if ($existingSubproc) {
            $subprocId = $existingSubproc->id;
        } else {
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
        }

        // ------------------------------------------------------------------------------
        // 5) DOCUMENTOS (exactamente los 7 que enviaste)
        //     Guardamos: name y code_default (PAM-04-###)
        //     Solo insertar si no existen
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

            // Solo insertar si no existe
            $existingDoc = DB::table('required_documents')
                ->where('process_id', $subprocId)
                ->where('code_default', $codeDefault)
                ->exists();

            if (!$existingDoc) {
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
}
