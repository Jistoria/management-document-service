<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeder que consolida todos los subsistemas principales utilizados por la universidad.
 *
 * Este seeder crea o actualiza las entradas de la tabla `subsystems`
 * para Docencia, Investigación, Vinculación y Gestión Administrativa.
 * Utiliza códigos literales (A, B, C, D) para mantener compatibilidad con
 * la nomenclatura establecida en los manuales institucionales.
 */
class AllSubsystemsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        // Lista de subsistemas estandarizados. Si se requiere añadir más
        // subsistemas en el futuro, agregarlos a este arreglo.
        $subsystems = [
            [
                'name' => 'Docencia',
                'code' => 'A',
                'code_numeric' => null,
            ],
            [
                'name' => 'Investigación',
                'code' => 'B',
                'code_numeric' => null,
            ],
            [
                'name' => 'Vinculación con la Sociedad',
                'code' => 'V',
                'code_numeric' => null,
            ],
            [
                'name' => 'Gestión Administrativa',
                'code' => 'G',
                'code_numeric' => null,
            ],
        ];

        foreach ($subsystems as $data) {
            // Obtener o generar un UUID para la entrada
            $id = DB::table('subsystems')
                ->where('code', $data['code'])
                ->value('id') ?: (string) Str::uuid7();

            // Crear o actualizar el registro en la base de datos
            DB::table('subsystems')->updateOrInsert(
                ['code' => $data['code']],
                [
                    'id' => $id,
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'code_numeric' => $data['code_numeric'],
                    'version' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'created_by' => 'system',
                    'updated_by' => 'system',
                ]
            );
        }
    }
}