<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InstitutionGraduationSeeder extends Seeder
{
    private const PREFIX = 'P';

    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        $subsystemId = DB::table('subsystems')->where('code', 'A')->value('id');
        
        if (!$subsystemId) {
            $this->command->error('❌ Subsistema Docencia no encontrado. Ejecute BaseInstitutionSeeder primero.');
            return;
        }

        $categoryId = DB::table('process_categories')
            ->where('subsystem_id', $subsystemId)
            ->where('name', 'GRADUACIÓN')
            ->value('id');

        if (!$categoryId) {
            $categoryId = (string) Str::uuid7();
            DB::table('process_categories')->insert([
                'id'               => $categoryId,
                'subsystem_id'     => $subsystemId,
                'name'             => 'GRADUACIÓN',
                'code'             => 'A',
                'numeric_code'     => null,
                'created_at'       => $now,
                'updated_at'       => $now,
                'version'          => 1,
                'created_by'       => 'system',
                'updated_by'       => 'system',
            ]);
        }

        $processId = DB::table('processes')
            ->where('process_category_id', $categoryId)
            ->where('name', 'TITULACIÓN')
            ->whereNull('parent_id')
            ->value('id');

        if (!$processId) {
            $processId = (string) Str::uuid7();
            DB::table('processes')->insert([
                'id'                  => $processId,
                'process_category_id' => $categoryId,
                'parent_id'           => null,
                'name'                => 'TITULACIÓN',
                'code'                => 'T',
                'numeric_code'        => null,
                'created_at'          => $now,
                'updated_at'          => $now,
                'version'             => 1,
                'created_by'          => 'system',
                'updated_by'          => 'system',
            ]);
        }

        $base3 = self::PREFIX . 'A' . 'T';

        $subprocesses = [
            ['n' => 1, 'name' => 'Titulación de estudiantes de grado'],
            ['n' => 2, 'name' => 'Emisión y registro de título de grado y postgrado'],
            ['n' => 3, 'name' => 'Titulación: UIC y Unidad de Titulación'],
            ['n' => 4, 'name' => 'Titulación de carreras técnicas y tecnológicas'],
        ];

        foreach ($subprocesses as $sp) {
            $code = sprintf('%s-%03d', $base3, $sp['n']);
            
            $existingSub = DB::table('processes')
                ->where('process_category_id', $categoryId)
                ->where('parent_id', $processId)
                ->where('code', $code)
                ->exists();

            if (!$existingSub) {
                DB::table('processes')->insert([
                    'id'                  => (string) Str::uuid7(),
                    'process_category_id' => $categoryId,
                    'parent_id'           => $processId,
                    'name'                => strtoupper($sp['name']),
                    'code'                => $code,
                    'numeric_code'        => null,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                    'version'             => 1,
                    'created_by'          => 'system',
                    'updated_by'          => 'system',
                ]);
            }
        }
    }
}
