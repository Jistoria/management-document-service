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
        $headOfficeId = DB::table('head_offices')->where('code', 'ULEAM-MAT')->value('id') 
            ?: (string) Str::uuid7();

        DB::table('head_offices')->updateOrInsert(
            ['code' => 'ULEAM-MAT'], // MAT para identificar que es la Matriz
            [
                'id'           => $headOfficeId,
                'name'         => 'ULEAM Sede Matriz Manta', // Nombre institucional correcto
                'code'         => 'ULEAM-MAT',
                'code_numeric' => '174',
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
            ['name' => 'Agropecuaria',                  'code' => 'AGR',  'code_numeric' => '213.1'],
            ['name' => 'Ingeniería en Agropecuaria',    'code' => 'IAGR', 'code_numeric' => '213.2'], // (o AGR según malla)
            ['name' => 'Agronegocios',                  'code' => 'AGRN', 'code_numeric' => '213.4'],
            ['name' => 'Agroindustria',                 'code' => 'AGRI', 'code_numeric' => '213.5'],
            ['name' => 'Ingeniería Ambiental',          'code' => 'IAMB', 'code_numeric' => '213.7'],
            ['name' => 'Tecnologías de la Información', 'code' => 'TDI',  'code_numeric' => '213.9'],
            ['name' => 'Software',                      'code' => 'SOFT', 'code_numeric' => '213.11'],
            ['name' => 'Biología',                      'code' => 'BIOL', 'code_numeric' => '213.14'],
            ['name' => 'Alimentos',                     'code' => 'ALIM', 'code_numeric' => '213.17'],
        ];

    
        foreach ($careers as $c) {
            // code ahora es la abreviatura oficial
            $careerCode = strtoupper(trim($c['code']));

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
