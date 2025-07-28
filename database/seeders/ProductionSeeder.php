<?php

namespace Database\Seeders;

use App\Models\HeadOffice;
use App\Models\Department;
use App\Models\Career;
use App\Models\Subsystem;
use App\Models\SubsystemGroup;
use App\Models\ProcessCategory;
use App\Models\Process;
use App\Models\DocumentType;
use App\Models\AcademicRole;
use App\Models\MetadataSchema;
use App\Models\StorageUnitType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Production Seeder - Creates essential base data for production environment
 *
 * This seeder creates the minimal, essential data structure needed for
 * a production document management system. It includes real organizational
 * structure and standard document types, roles, and schemas.
 */
class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds for production environment.
     */
    public function run(): void
    {
        $this->command->info('🏭 Iniciando seeder de producción...');

        // Disable foreign key checks for faster seeding (PostgreSQL compatible)
        DB::unprepared('SET session_replication_role = replica;');

        try {
            // Create base organizational structure
            $this->createBaseOrganizationalStructure();

            // Create standard subsystems
            $this->createStandardSubsystems();

            // Create subsystem groups and establish relationships
            $this->createSubsystemGroups();

            // Create subsystem-entity relationships
            $this->createSubsystemEntityRelationships();

            // Create standard document types
            $this->createStandardDocumentTypes();

            // Create standard academic roles
            $this->createStandardAcademicRoles();

            // Create base metadata schemas
            $this->createBaseMetadataSchemas();

            // Create storage unit types
            $this->createStorageUnitTypes();

            // Create initial process structure
            $this->createInitialProcessStructure();

            $this->command->info('✅ Seeder de producción completado exitosamente');
        } finally {
            // Re-enable foreign key checks (PostgreSQL compatible)
            DB::unprepared('SET session_replication_role = DEFAULT;');
        }
    }

    /**
     * Create base organizational structure
     */
    private function createBaseOrganizationalStructure(): void
    {
        $this->command->info('🏢 Creando estructura organizacional base...');

        // Sede Principal
        $sedePrincipal = HeadOffice::firstOrCreate([
            'code' => 'PRINCIPAL'
        ], [
            'name' => 'Sede Principal',
            'created_by' => 'system',
            'version' => 1,
        ]);

        // Departamentos base
        $departamentos = [
            [
                'head_office_id' => $sedePrincipal->id,
                'name' => 'Facultad de Ingeniería y Tecnología',
                'code' => 'INGYTEC',
                'created_by' => 'system',
                'version' => 1,
            ],
            [
                'head_office_id' => $sedePrincipal->id,
                'name' => 'Facultad de Ciencias',
                'code' => 'CIENCIAS',
                'created_by' => 'system',
                'version' => 1,
            ],
            [
                'head_office_id' => $sedePrincipal->id,
                'name' => 'Facultad de Ciencias de la Salud',
                'code' => 'SALUD',
                'created_by' => 'system',
                'version' => 1,
            ],
            [
                'head_office_id' => $sedePrincipal->id,
                'name' => 'Facultad de Ciencias Económicas y Administrativas',
                'code' => 'ECONOMICAS',
                'created_by' => 'system',
                'version' => 1,
            ],
        ];

        foreach ($departamentos as $deptData) {
            $department = Department::firstOrCreate([
                'code' => $deptData['code']
            ], $deptData);
            $this->createCareersForDepartment($department);
        }

        $this->command->info("   ✓ Head Offices: " . HeadOffice::count());
        $this->command->info("   ✓ Departments: " . Department::count());
        $this->command->info("   ✓ Careers: " . Career::count());
    }

    /**
     * Create careers for a specific department
     */
    private function createCareersForDepartment(Department $department): void
    {
        $careersByDepartment = [
            'INGYTEC' => [
                ['name' => 'Ingeniería de Sistemas', 'code' => 'INGSIST'],
                ['name' => 'Ingeniería Civil', 'code' => 'INGCIV'],
                ['name' => 'Ingeniería Industrial', 'code' => 'INGIND'],
                ['name' => 'Ingeniería Electrónica', 'code' => 'INGELEC'],
                ['name' => 'Ingeniería de Software', 'code' => 'INGSOFT'],
            ],
            'CIENCIAS' => [
                ['name' => 'Licenciatura en Matemáticas', 'code' => 'LICMAT'],
                ['name' => 'Licenciatura en Física', 'code' => 'LICFIS'],
                ['name' => 'Licenciatura en Química', 'code' => 'LICQUI'],
                ['name' => 'Licenciatura en Biología', 'code' => 'LICBIO'],
            ],
            'SALUD' => [
                ['name' => 'Medicina', 'code' => 'MEDICINA'],
                ['name' => 'Enfermería', 'code' => 'ENFERM'],
                ['name' => 'Fisioterapia', 'code' => 'FISIO'],
                ['name' => 'Psicología', 'code' => 'PSICO'],
            ],
            'ECONOMICAS' => [
                ['name' => 'Administración de Empresas', 'code' => 'ADMIN'],
                ['name' => 'Contaduría Pública', 'code' => 'CONTA'],
                ['name' => 'Economía', 'code' => 'ECON'],
                ['name' => 'Mercadeo', 'code' => 'MERCA'],
            ],
        ];

        $careers = $careersByDepartment[$department->code] ?? [];

        foreach ($careers as $careerData) {
            Career::firstOrCreate([
                'code' => $careerData['code']
            ], [
                'department_id' => $department->id,
                'name' => $careerData['name'],
                'code' => $careerData['code'],
                'created_by' => 'system',
                'version' => 1,
            ]);
        }
    }

    /**
     * Create standard subsystems
     */
    private function createStandardSubsystems(): void
    {
        $this->command->info('⚙️ Creando subsistemas estándar...');

        $subsystems = [
            [
                'name' => 'Sistema de Gestión Académica',
                'code' => 'SGA',
            ],
            [
                'name' => 'Sistema de Biblioteca Digital',
                'code' => 'BIBLIOTECA',
            ],
            [
                'name' => 'Sistema de Laboratorios',
                'code' => 'LABORATORIOS',
            ],
            [
                'name' => 'Sistema de Proyectos de Investigación',
                'code' => 'INVESTIGACION',
            ],
            [
                'name' => 'Sistema de Prácticas Profesionales',
                'code' => 'PRACTICAS',
            ],
            [
                'name' => 'Sistema de Graduación',
                'code' => 'GRADUACION',
            ],
        ];

        foreach ($subsystems as $subsystemData) {
            Subsystem::firstOrCreate([
                'code' => $subsystemData['code']
            ], $subsystemData);
        }

        $this->command->info("   ✓ Subsystems: " . Subsystem::count());
    }

    /**
     * Create subsystem groups and establish relationships
     */
    private function createSubsystemGroups(): void
    {
        $this->command->info('🏷️ Creando grupos de subsistemas...');

        $subsystemGroups = [
            [
                'name' => 'Gestión Académica',
                'code' => 'ACADEMICO',
                'description' => 'Subsistemas relacionados con la gestión académica institucional',
                'is_public' => true,
                'created_by' => 'system',
                'updated_by' => 'system',
            ],
            [
                'name' => 'Investigación y Desarrollo',
                'code' => 'INVESTIGACION',
                'description' => 'Subsistemas dedicados a la investigación y desarrollo científico',
                'is_public' => true,
                'created_by' => 'system',
                'updated_by' => 'system',
            ],
            [
                'name' => 'Servicios Estudiantiles',
                'code' => 'SERVICIOS',
                'description' => 'Subsistemas que brindan servicios a los estudiantes',
                'is_public' => true,
                'created_by' => 'system',
                'updated_by' => 'system',
            ],
        ];

        foreach ($subsystemGroups as $groupData) {
            $group = SubsystemGroup::firstOrCreate([
                'code' => $groupData['code']
            ], $groupData);

            // Asociar subsistemas con grupos
            $this->associateSubsystemsWithGroup($group);
        }

        $this->command->info("   ✓ Subsystem Groups: " . SubsystemGroup::count());
    }

    /**
     * Associate subsystems with their respective groups
     */
    private function associateSubsystemsWithGroup(SubsystemGroup $group): void
    {
        $subsystemsByGroup = [
            'ACADEMICO' => ['SGA', 'GRADUACION'],
            'INVESTIGACION' => ['INVESTIGACION'],
            'SERVICIOS' => ['BIBLIOTECA', 'LABORATORIOS', 'PRACTICAS'],
        ];

        $subsystemCodes = $subsystemsByGroup[$group->code] ?? [];

        foreach ($subsystemCodes as $code) {
            $subsystem = Subsystem::where('code', $code)->first();
            if ($subsystem) {
                $group->subsystems()->syncWithoutDetaching([$subsystem->id]);
            }
        }
    }

    /**
     * Create subsystem-entity relationships using morphedByMany
     */
    private function createSubsystemEntityRelationships(): void
    {
        $this->command->info('🔗 Creando relaciones subsistema-entidades...');

        // Obtener subsistemas
        $sga = Subsystem::where('code', 'SGA')->first();
        $biblioteca = Subsystem::where('code', 'BIBLIOTECA')->first();
        $laboratorios = Subsystem::where('code', 'LABORATORIOS')->first();
        $investigacion = Subsystem::where('code', 'INVESTIGACION')->first();
        $practicas = Subsystem::where('code', 'PRACTICAS')->first();
        $graduacion = Subsystem::where('code', 'GRADUACION')->first();

        // Obtener entidades
        $sedePrincipal = HeadOffice::where('code', 'PRINCIPAL')->first();
        
        $deptIngenieria = Department::where('code', 'INGYTEC')->first();
        $deptCiencias = Department::where('code', 'CIENCIAS')->first();
        $deptSalud = Department::where('code', 'SALUD')->first();
        $deptEconomicas = Department::where('code', 'ECONOMICAS')->first();

        // Crear relaciones con sede principal para todos los subsistemas
        if ($sedePrincipal) {
            $subsystems = [$sga, $biblioteca, $laboratorios, $investigacion, $practicas, $graduacion];
            foreach ($subsystems as $subsystem) {
                if ($subsystem) {
                    $subsystem->headOffices()->syncWithoutDetaching([$sedePrincipal->id]);
                }
            }
        }

        // Relaciones específicas por departamento
        if ($sga) {
            // SGA se relaciona con todos los departamentos
            $departments = [$deptIngenieria, $deptCiencias, $deptSalud, $deptEconomicas];
            foreach ($departments as $dept) {
                if ($dept) {
                    $sga->departments()->syncWithoutDetaching([$dept->id]);
                    
                    // También relacionar con todas las carreras del departamento
                    $careers = Career::where('department_id', $dept->id)->get();
                    foreach ($careers as $career) {
                        $sga->careers()->syncWithoutDetaching([$career->id]);
                    }
                }
            }
        }

        if ($biblioteca) {
            // Biblioteca se relaciona con todos los departamentos y carreras
            $departments = [$deptIngenieria, $deptCiencias, $deptSalud, $deptEconomicas];
            foreach ($departments as $dept) {
                if ($dept) {
                    $biblioteca->departments()->syncWithoutDetaching([$dept->id]);
                    
                    $careers = Career::where('department_id', $dept->id)->get();
                    foreach ($careers as $career) {
                        $biblioteca->careers()->syncWithoutDetaching([$career->id]);
                    }
                }
            }
        }

        if ($laboratorios) {
            // Laboratorios principalmente para Ingeniería y Ciencias
            $techDepartments = [$deptIngenieria, $deptCiencias];
            foreach ($techDepartments as $dept) {
                if ($dept) {
                    $laboratorios->departments()->syncWithoutDetaching([$dept->id]);
                    
                    $careers = Career::where('department_id', $dept->id)->get();
                    foreach ($careers as $career) {
                        $laboratorios->careers()->syncWithoutDetaching([$career->id]);
                    }
                }
            }
        }

        if ($investigacion) {
            // Investigación se relaciona con todos los departamentos
            $departments = [$deptIngenieria, $deptCiencias, $deptSalud, $deptEconomicas];
            foreach ($departments as $dept) {
                if ($dept) {
                    $investigacion->departments()->syncWithoutDetaching([$dept->id]);
                    
                    $careers = Career::where('department_id', $dept->id)->get();
                    foreach ($careers as $career) {
                        $investigacion->careers()->syncWithoutDetaching([$career->id]);
                    }
                }
            }
        }

        if ($practicas) {
            // Prácticas profesionales se relaciona con todos los departamentos
            $departments = [$deptIngenieria, $deptCiencias, $deptSalud, $deptEconomicas];
            foreach ($departments as $dept) {
                if ($dept) {
                    $practicas->departments()->syncWithoutDetaching([$dept->id]);
                    
                    $careers = Career::where('department_id', $dept->id)->get();
                    foreach ($careers as $career) {
                        $practicas->careers()->syncWithoutDetaching([$career->id]);
                    }
                }
            }
        }

        if ($graduacion) {
            // Graduación se relaciona con todos los departamentos
            $departments = [$deptIngenieria, $deptCiencias, $deptSalud, $deptEconomicas];
            foreach ($departments as $dept) {
                if ($dept) {
                    $graduacion->departments()->syncWithoutDetaching([$dept->id]);
                    
                    $careers = Career::where('department_id', $dept->id)->get();
                    foreach ($careers as $career) {
                        $graduacion->careers()->syncWithoutDetaching([$career->id]);
                    }
                }
            }
        }

        $this->command->info("   ✓ Relaciones subsistema-entidades creadas exitosamente");
    }

    /**
     * Create standard document types
     */
    private function createStandardDocumentTypes(): void
    {
        $this->command->info('📄 Creando tipos de documento estándar...');

        $documentTypes = [
            // Documentos académicos
            ['name' => 'Acta de Grado', 'code' => 'ACTA_GRADO'],
            ['name' => 'Certificado de Estudio', 'code' => 'CERT_ESTUDIO'],
            ['name' => 'Constancia de Matrícula', 'code' => 'CONST_MATRICULA'],
            ['name' => 'Diploma', 'code' => 'DIPLOMA'],
            ['name' => 'Transcript Académico', 'code' => 'TRANSCRIPT'],

            // Documentos de investigación
            ['name' => 'Proyecto de Investigación', 'code' => 'PROY_INVEST'],
            ['name' => 'Informe de Investigación', 'code' => 'INF_INVEST'],
            ['name' => 'Artículo Científico', 'code' => 'ART_CIENTIFICO'],
            ['name' => 'Tesis de Grado', 'code' => 'TESIS'],
            ['name' => 'Trabajo de Grado', 'code' => 'TRAB_GRADO'],

            // Documentos administrativos
            ['name' => 'Resolución', 'code' => 'RESOLUCION'],
            ['name' => 'Memorando', 'code' => 'MEMORANDO'],
            ['name' => 'Circular', 'code' => 'CIRCULAR'],
            ['name' => 'Acta de Reunión', 'code' => 'ACTA_REUNION'],
            ['name' => 'Informe Administrativo', 'code' => 'INF_ADMIN'],

            // Documentos de laboratorio
            ['name' => 'Protocolo de Laboratorio', 'code' => 'PROT_LAB'],
            ['name' => 'Informe de Laboratorio', 'code' => 'INF_LAB'],
            ['name' => 'Manual de Procedimientos', 'code' => 'MAN_PROC'],

            // Documentos de prácticas
            ['name' => 'Convenio de Práctica', 'code' => 'CONV_PRACTICA'],
            ['name' => 'Informe de Práctica', 'code' => 'INF_PRACTICA'],
            ['name' => 'Evaluación de Práctica', 'code' => 'EVAL_PRACTICA'],
        ];

        foreach ($documentTypes as $docTypeData) {
            DocumentType::firstOrCreate([
                'code' => $docTypeData['code']
            ], $docTypeData);
        }

        $this->command->info("   ✓ Document Types: " . DocumentType::count());
    }

    /**
     * Create standard academic roles
     */
    private function createStandardAcademicRoles(): void
    {
        $this->command->info('👥 Creando roles académicos estándar...');

        $academicRoles = [
            // Roles estudiantiles
            ['name' => 'Estudiante Pregrado', 'code' => 'EST_PREGRADO'],
            ['name' => 'Estudiante Posgrado', 'code' => 'EST_POSGRADO'],
            ['name' => 'Estudiante Intercambio', 'code' => 'EST_INTERCAMBIO'],

            // Roles docentes
            ['name' => 'Profesor Catedrático', 'code' => 'PROF_CATEDRATICO'],
            ['name' => 'Profesor Asociado', 'code' => 'PROF_ASOCIADO'],
            ['name' => 'Profesor Asistente', 'code' => 'PROF_ASISTENTE'],
            ['name' => 'Profesor de Hora Cátedra', 'code' => 'PROF_HORA_CATEDRA'],

            // Roles administrativos académicos
            ['name' => 'Decano', 'code' => 'DECANO'],
            ['name' => 'Vicedecano', 'code' => 'VICEDECANO'],
            ['name' => 'Director de Programa', 'code' => 'DIR_PROGRAMA'],
            ['name' => 'Coordinador Académico', 'code' => 'COORD_ACADEMICO'],
            ['name' => 'Secretario Académico', 'code' => 'SEC_ACADEMICO'],

            // Roles de investigación
            ['name' => 'Investigador Principal', 'code' => 'INV_PRINCIPAL'],
            ['name' => 'Investigador Asociado', 'code' => 'INV_ASOCIADO'],
            ['name' => 'Asistente de Investigación', 'code' => 'ASIST_INVESTIGACION'],

            // Roles de laboratorio
            ['name' => 'Jefe de Laboratorio', 'code' => 'JEFE_LAB'],
            ['name' => 'Técnico de Laboratorio', 'code' => 'TEC_LAB'],

            // Roles de biblioteca
            ['name' => 'Bibliotecario', 'code' => 'BIBLIOTECARIO'],
            ['name' => 'Asistente de Biblioteca', 'code' => 'ASIST_BIBLIOTECA'],
        ];

        foreach ($academicRoles as $roleData) {
            AcademicRole::firstOrCreate([
                'code' => $roleData['code']
            ], $roleData);
        }

        $this->command->info("   ✓ Academic Roles: " . AcademicRole::count());
    }

    /**
     * Create base metadata schemas
     */
    private function createBaseMetadataSchemas(): void
    {
        $this->command->info('📋 Creando esquemas de metadatos base...');

        $metadataSchemas = [
            [
                'name' => 'Esquema Básico de Documento',
                'description' => 'Esquema básico aplicable a cualquier documento del sistema',
                'version' => 1,
                'is_canonical' => true,
            ],
            [
                'name' => 'Esquema de Trabajo de Grado',
                'description' => 'Esquema específico para trabajos de grado y tesis',
                'version' => 1,
                'is_canonical' => true,
            ],
            [
                'name' => 'Esquema de Investigación',
                'description' => 'Esquema para proyectos e informes de investigación',
                'version' => 1,
                'is_canonical' => true,
            ]
        ];

        foreach ($metadataSchemas as $schemaData) {
            MetadataSchema::firstOrCreate([
                'name' => $schemaData['name']
            ], $schemaData);
        }

        $this->command->info("   ✓ Metadata Schemas: " . MetadataSchema::count());
    }

    /**
     * Create storage unit types
     */
    private function createStorageUnitTypes(): void
    {
        $this->command->info('🗃️ Creando tipos de unidades de almacenamiento...');

        $storageUnitTypes = [
            [
                'name' => 'Archivo Digital',
                'code' => 'DIGITAL',
                'level' => 1,
            ],
            [
                'name' => 'Archivo Físico',
                'code' => 'FISICO',
                'level' => 1,
            ],
            [
                'name' => 'Archivo Híbrido',
                'code' => 'HIBRIDO',
                'level' => 2,
            ],
            [
                'name' => 'Repositorio Institucional',
                'code' => 'REPOSITORIO',
                'level' => 3,
            ],
        ];

        foreach ($storageUnitTypes as $storageTypeData) {
            StorageUnitType::firstOrCreate([
                'code' => $storageTypeData['code']
            ], $storageTypeData);
        }

        $this->command->info("   ✓ Storage Unit Types: " . StorageUnitType::count());
    }

    /**
     * Create initial process structure
     */
    private function createInitialProcessStructure(): void
    {
        $this->command->info('🔄 Creando estructura inicial de procesos...');

        // Get subsystems
        $sga = Subsystem::where('code', 'SGA')->first();
        $investigacion = Subsystem::where('code', 'INVESTIGACION')->first();
        $graduacion = Subsystem::where('code', 'GRADUACION')->first();

        if ($sga) {
            $admisiones = ProcessCategory::firstOrCreate([
                'code' => 'ADMISIONES'
            ], [
                'subsystem_id' => $sga->id,
                'name' => 'Procesos de Admisiones',
                'code' => 'ADMISIONES',
            ]);

            Process::firstOrCreate([
                'code' => 'INSCRIPCION'
            ], [
                'process_category_id' => $admisiones->id,
                'name' => 'Inscripción de Nuevos Estudiantes',
                'code' => 'INSCRIPCION',
                'order' => 1,
            ]);
        }

        if ($investigacion) {
            $proyectos = ProcessCategory::firstOrCreate([
                'code' => 'PROYECTOS'
            ], [
                'subsystem_id' => $investigacion->id,
                'name' => 'Gestión de Proyectos',
                'code' => 'PROYECTOS',
            ]);

            Process::firstOrCreate([
                'code' => 'REG_PROYECTO'
            ], [
                'process_category_id' => $proyectos->id,
                'name' => 'Registro de Proyectos de Investigación',
                'code' => 'REG_PROYECTO',
                'order' => 1,
            ]);
        }

        if ($graduacion) {
            $titulacion = ProcessCategory::firstOrCreate([
                'code' => 'TITULACION'
            ], [
                'subsystem_id' => $graduacion->id,
                'name' => 'Procesos de Titulación',
                'code' => 'TITULACION',
            ]);

            Process::firstOrCreate([
                'code' => 'GRADUACION'
            ], [
                'process_category_id' => $titulacion->id,
                'name' => 'Proceso de Graduación',
                'code' => 'GRADUACION',
                'order' => 1,
            ]);
        }

        $this->command->info("   ✓ Process Categories: " . ProcessCategory::count());
        $this->command->info("   ✓ Processes: " . Process::count());
    }
}
