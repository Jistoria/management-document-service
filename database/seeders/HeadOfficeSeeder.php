<?php

namespace Database\Seeders;

use App\Models\HeadOffice;
use App\Models\Department;
use App\Models\Career;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HeadOfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear sedes principales
        $headOffices = [
            [
                'id' => Str::uuid(),
                'name' => 'Sede Central',
                'code' => 'CENTRAL',
                'created_by' => 'system',
                'version' => 1,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Sede Norte',
                'code' => 'NORTE',
                'created_by' => 'system',
                'version' => 1,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Sede Sur',
                'code' => 'SUR',
                'created_by' => 'system',
                'version' => 1,
            ],
        ];

        foreach ($headOffices as $headOfficeData) {
            $headOffice = HeadOffice::create($headOfficeData);

            // Crear departamentos para cada sede
            $departments = [
                [
                    'id' => Str::uuid(),
                    'head_office_id' => $headOffice->id,
                    'name' => 'Facultad de Ingeniería',
                    'code' => 'FI',
                    'created_by' => 'system',
                    'version' => 1,
                ],
                [
                    'id' => Str::uuid(),
                    'head_office_id' => $headOffice->id,
                    'name' => 'Facultad de Ciencias',
                    'code' => 'FC',
                    'created_by' => 'system',
                    'version' => 1,
                ],
            ];

            foreach ($departments as $departmentData) {
                $department = Department::create($departmentData);

                // Crear carreras para cada departamento
                if ($departmentData['code'] === 'FI') {
                    $careers = [
                        [
                            'id' => Str::uuid(),
                            'department_id' => $department->id,
                            'name' => 'Ingeniería de Sistemas',
                            'code' => 'IS',
                            'created_by' => 'system',
                            'version' => 1,
                        ],
                        [
                            'id' => Str::uuid(),
                            'department_id' => $department->id,
                            'name' => 'Ingeniería Industrial',
                            'code' => 'II',
                            'created_by' => 'system',
                            'version' => 1,
                        ],
                    ];
                } else {
                    $careers = [
                        [
                            'id' => Str::uuid(),
                            'department_id' => $department->id,
                            'name' => 'Biología',
                            'code' => 'BIO',
                            'created_by' => 'system',
                            'version' => 1,
                        ],
                        [
                            'id' => Str::uuid(),
                            'department_id' => $department->id,
                            'name' => 'Química',
                            'code' => 'QUI',
                            'created_by' => 'system',
                            'version' => 1,
                        ],
                    ];
                }

                foreach ($careers as $careerData) {
                    Career::create($careerData);
                }
            }
        }
    }
}
