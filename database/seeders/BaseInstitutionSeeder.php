<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Seeder base que crea la estructura organizacional básica:
 * - Head Office (Sede)
 * - Department (Facultad)
 * - Subsystem (Docencia)
 * 
 * Este seeder se ejecuta PRIMERO para evitar duplicaciones
 */
class BaseInstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        // =========================================================================
        // 1) HEAD OFFICE (Sede)
        // =========================================================================
        $headOfficeId = DB::table('head_offices')->where('code', 'ULEAM-MAN')->value('id') 
            ?: (string) Str::uuid7();

        DB::table('head_offices')->updateOrInsert(
            ['code' => 'ULEAM-MAN'],
            [
                'id'           => $headOfficeId,
                'name'         => 'ULEAM Extensión Manta',
                'code'         => 'ULEAM-MAN',
                'code_numeric' => null,
                'created_at'   => $now,
                'updated_at'   => $now,
                'version'      => 1,
                'created_by'   => 'system',
                'updated_by'   => 'system',
            ]
        );

        // =========================================================================
        // 2) DEPARTMENT (Facultad de Ciencias de la Vida y Tecnologías)
        // =========================================================================
        $deptId = DB::table('departments')
            ->where('head_office_id', $headOfficeId)
            ->where('code', 'FCVT')
            ->value('id') ?: (string) Str::uuid7();

        DB::table('departments')->updateOrInsert(
            ['head_office_id' => $headOfficeId, 'code' => 'FCVT'],
            [
                'id'             => $deptId,
                'head_office_id' => $headOfficeId,
                'name'           => 'Facultad de Ciencias de la Vida y Tecnologías',
                'code'           => 'FCVT',
                'code_numeric'   => '213',
                'created_at'     => $now,
                'updated_at'     => $now,
                'version'        => 1,
                'created_by'     => 'system',
                'updated_by'     => 'system',
            ]
        );

        // =========================================================================
        // 3) CAREERS (Carreras vigentes)
        // =========================================================================
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
            $careerCode = Str::of($c['name'])->upper()->slug('_')->value();
            
            DB::table('careers')->updateOrInsert(
                ['department_id' => $deptId, 'code' => $careerCode],
                [
                    'id'            => DB::table('careers')
                        ->where('department_id', $deptId)
                        ->where('code', $careerCode)
                        ->value('id') ?: (string) Str::uuid7(),
                    'department_id' => $deptId,
                    'name'          => $c['name'],
                    'code'          => $careerCode,
                    'code_numeric'  => $c['code_numeric'],
                    'created_at'    => $now,
                    'updated_at'    => $now,
                    'version'       => 1,
                    'created_by'    => 'system',
                    'updated_by'    => 'system',
                ]
            );
        }

        // =========================================================================
        // 4) SUBSYSTEM: DOCENCIA (code = 'A')
        //    Este es el único lugar donde se crea el subsistema
        // =========================================================================
        $subsystemId = DB::table('subsystems')->where('code', 'A')->value('id') 
            ?: (string) Str::uuid7();

        DB::table('subsystems')->updateOrInsert(
            ['code' => 'A'],
            [
                'id'           => $subsystemId,
                'name'         => 'Docencia',
                'code'         => 'A',
                'code_numeric' => null,
                'created_at'   => $now,
                'updated_at'   => $now,
                'version'      => 1,
                'created_by'   => 'system',
                'updated_by'   => 'system',
            ]
        );

        $this->command->info(" Estructura base creada: Sede, Facultad, Carreras y Subsistema Docencia");
    }
}
