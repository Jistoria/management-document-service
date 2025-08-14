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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Testing Seeder - Uses factories to generate random test data
 *
 * This seeder is designed for development and testing environments.
 * It creates a large amount of varied, realistic test data using model factories.
 */
class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds for testing environment.
     */
    public function run(): void
    {
        $this->command->info('🧪 Iniciando seeder de testing...');

        // Disable foreign key checks for faster seeding (PostgreSQL compatible)
        DB::unprepared('SET session_replication_role = replica;');

        try {
            // Clear existing data in testing
            $this->clearTestingData();

            // Crear estructura organizacional usando factories
            $this->createOrganizationalStructure();

            // Crear datos de soporte
            $this->createSupportData();

            // Crear entidades de documentos
            $this->call(DocumentTestingSeeder::class);

            // Crear grupos de subsistemas y relaciones
            $this->createSubsystemGroupsAndRelationships();

            $this->command->info('✅ Seeder de testing completado exitosamente');
        } finally {
            // Re-enable foreign key checks (PostgreSQL compatible)
            DB::unprepared('SET session_replication_role = DEFAULT;');
        }
    }

    /**
     * Clear existing testing data
     */
    private function clearTestingData(): void
    {
        $this->command->info('🧹 Limpiando datos existentes...');

        // Clear in reverse dependency order
        DB::table('subsystem_entity_links')->delete();
        DB::table('subsystem_group_links')->delete();
        Career::query()->forceDelete();
        Department::query()->forceDelete();
        HeadOffice::query()->forceDelete();
        SubsystemGroup::query()->forceDelete();
        Subsystem::query()->forceDelete();
        ProcessCategory::query()->forceDelete();
        Process::query()->forceDelete();
        DocumentType::query()->forceDelete();
        AcademicRole::query()->forceDelete();
        MetadataSchema::query()->forceDelete();
    }

    /**
     * Create organizational structure using factories
     */
    private function createOrganizationalStructure(): void
    {
        $this->command->info('🏢 Creando estructura organizacional...');

        // Create 3-5 head offices with departments and careers
        HeadOffice::factory()
            ->count(4)
            ->has(
                Department::factory()
                    ->count(3) // 3 departments per head office
                    ->has(
                        Career::factory()
                            ->count(5) // 5 careers per department
                    )
            )
            ->create();

        // Create some specific scenarios for testing
        $this->createSpecificTestScenarios();

        $this->command->info("   ✓ Head Offices: " . HeadOffice::count());
        $this->command->info("   ✓ Departments: " . Department::count());
        $this->command->info("   ✓ Careers: " . Career::count());
    }

    /**
     * Create specific test scenarios
     */
    private function createSpecificTestScenarios(): void
    {
        // Scenario 1: Main campus with engineering, sciences, and medicine
        $mainCampus = HeadOffice::factory()->mainCampus()->create();

        $engineering = Department::factory()
            ->engineering()
            ->forHeadOffice($mainCampus)
            ->create();

        $sciences = Department::factory()
            ->sciences()
            ->forHeadOffice($mainCampus)
            ->create();

        $medicine = Department::factory()
            ->medicine()
            ->forHeadOffice($mainCampus)
            ->create();

        // Create specific careers for each department
        Career::factory()
            ->engineering()
            ->forDepartment($engineering)
            ->count(6)
            ->create();

        Career::factory()
            ->sciences()
            ->forDepartment($sciences)
            ->count(4)
            ->create();

        Career::factory()
            ->medicine()
            ->forDepartment($medicine)
            ->count(5)
            ->create();

        // Scenario 2: Regional campus with limited offerings
        $regionalCampus = HeadOffice::factory()->regional()->create();

        $regionalDept = Department::factory()
            ->forHeadOffice($regionalCampus)
            ->create([
                'name' => 'Departamento Regional de Ciencias Básicas',
                'code' => 'REGCB'
            ]);

        Career::factory()
            ->forDepartment($regionalDept)
            ->count(3)
            ->create();
    }

    /**
     * Create support data using factories and realistic data
     */
    private function createSupportData(): void
    {
        $this->command->info('📋 Creando datos de soporte...');

        // Create subsystems
        $subsystems = [
            ['name' => 'Sistema de Gestión Académica', 'code' => 'SGA'],
            ['name' => 'Sistema de Biblioteca', 'code' => 'BIBLIO'],
            ['name' => 'Sistema de Laboratorios', 'code' => 'LAB'],
            ['name' => 'Sistema de Proyectos', 'code' => 'PROJ'],
            ['name' => 'Sistema de Prácticas', 'code' => 'PRACT'],
        ];

        foreach ($subsystems as $subsystemData) {
            Subsystem::create($subsystemData);
        }

        // Create academic roles
        $academicRoles = [
            ['name' => 'Estudiante', 'code' => 'EST'],
            ['name' => 'Docente', 'code' => 'DOC'],
            ['name' => 'Coordinador', 'code' => 'COORD'],
            ['name' => 'Director', 'code' => 'DIR'],
            ['name' => 'Decano', 'code' => 'DEC'],
            ['name' => 'Secretario Académico', 'code' => 'SECACAD'],
            ['name' => 'Jefe de Laboratorio', 'code' => 'JEFLAB'],
        ];

        foreach ($academicRoles as $roleData) {
            AcademicRole::create($roleData);
        }

        $this->command->info("   ✓ Subsystems: " . Subsystem::count());
        $this->command->info("   ✓ Academic Roles: " . AcademicRole::count());
    }

    /**
     * Create subsystem groups and establish relationships for testing
     */
    private function createSubsystemGroupsAndRelationships(): void
    {
        $this->command->info('🔗 Creando grupos de subsistemas y relaciones...');

        // Create subsystem groups
        $groups = [
            [
                'name' => 'Sistemas Académicos',
                'code' => 'ACAD_TEST',
                'description' => 'Grupo de subsistemas académicos para testing',
                'is_public' => true,
                'created_by' => 'system',
                'updated_by' => 'system',
            ],
            [
                'name' => 'Sistemas de Apoyo',
                'code' => 'APOYO_TEST',
                'description' => 'Grupo de subsistemas de apoyo para testing',
                'is_public' => true,
                'created_by' => 'system',
                'updated_by' => 'system',
            ]
        ];

        foreach ($groups as $groupData) {
            SubsystemGroup::create($groupData);
        }

        // Associate subsystems with groups
        $acadGroup = SubsystemGroup::where('code', 'ACAD_TEST')->first();
        $supportGroup = SubsystemGroup::where('code', 'APOYO_TEST')->first();

        $sga = Subsystem::where('code', 'SGA')->first();
        $pract = Subsystem::where('code', 'PRACT')->first();
        $biblio = Subsystem::where('code', 'BIBLIO')->first();
        $lab = Subsystem::where('code', 'LAB')->first();

        if ($acadGroup && $sga) $acadGroup->subsystems()->attach($sga->id);
        if ($acadGroup && $pract) $acadGroup->subsystems()->attach($pract->id);
        if ($supportGroup && $biblio) $supportGroup->subsystems()->attach($biblio->id);
        if ($supportGroup && $lab) $supportGroup->subsystems()->attach($lab->id);

        // Create entity relationships randomly for testing
        $this->createRandomEntityRelationships();

        $this->command->info("   ✓ Subsystem Groups: " . SubsystemGroup::count());
        $this->command->info("   ✓ Relaciones establecidas para testing");
    }

    /**
     * Create random entity relationships for testing
     */
    private function createRandomEntityRelationships(): void
    {
        $subsystems = Subsystem::all();
        $headOffices = HeadOffice::all();
        $departments = Department::all();
        $careers = Career::all();

        foreach ($subsystems as $subsystem) {
            // Associate with random head offices (1-2 per subsystem)
            $randomHeadOffices = $headOffices->random(min(2, $headOffices->count()));
            foreach ($randomHeadOffices as $headOffice) {
                $subsystem->headOffices()->syncWithoutDetaching([$headOffice->id]);
            }

            // Associate with random departments (2-4 per subsystem)
            $randomDepartments = $departments->random(min(3, $departments->count()));
            foreach ($randomDepartments as $department) {
                $subsystem->departments()->syncWithoutDetaching([$department->id]);
            }

            // Associate with random careers (3-6 per subsystem)
            $randomCareers = $careers->random(min(5, $careers->count()));
            foreach ($randomCareers as $career) {
                $subsystem->careers()->syncWithoutDetaching([$career->id]);
            }
        }
    }
}
