<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $environment = app()->environment();

        $this->command->info("🌍 Ejecutando seeders para entorno: {$environment}");

        // =========================================================================
        // ORDEN DE EJECUCIÓN DE SEEDERS
        // =========================================================================
        // 
        // 1. Estructura Base (Head Office, Department, Careers)
        // 2. Subsistemas (Docencia, Investigación, Vinculación, Gestión)
        // 3. Procesos de Graduación (Docencia)
        // 4. Procesos de Admisión/Matrícula (Docencia)
        // 5. Procesos de Desarrollo Estudiantil (Docencia)
        // 6. Procesos de Vinculación (Vinculación con la Sociedad)
        // 7. Metadatos de documentos específicos
        // 8. Campos de metadatos universitarios genéricos
        // =========================================================================

        if ($environment === 'production') {
            $this->call([
                // ─────────────────────────────────────────────────────────────
                // PASO 1: Estructura Organizacional Base
                // ─────────────────────────────────────────────────────────────
                BaseInstitutionSeeder::class,
                // Crea:
                // - Head Office: ULEAM Sede Matriz Manta
                // - Department: Facultad de Ciencias de la Vida y Tecnologías
                // - Careers: 9 carreras (AGR, IAGR, AGRN, AGRI, IAMB, TDI, SOFT, BIOL, ALIM)
                // - Subsystem: Docencia (code 'A')

                AdditionalDocumentTypesSeeder::class,
                // Crea tipos de documentos adicionales (INF, PLAN, REG, EVAL, SOL

                // ─────────────────────────────────────────────────────────────
                // PASO 2: Subsistemas Adicionales
                // ─────────────────────────────────────────────────────────────
                AllSubsystemsSeeder::class,
                // Crea/actualiza:
                // - Subsystem: Docencia (code 'A') - ya existe, solo actualiza
                // - Subsystem: Investigación (code 'B')
                // - Subsystem: Vinculación con la Sociedad (code 'V')
                // - Subsystem: Gestión Administrativa (code 'G')

                // ─────────────────────────────────────────────────────────────
                // PASO 3: Procesos del Subsistema DOCENCIA (code 'A')
                // ─────────────────────────────────────────────────────────────
                InstitutionGraduationSeeder::class,
                // Crea procesos de graduación bajo Docencia

                AdmissionMatriculaDocumentsSeeder::class,
                // Crea procesos de admisión/matrícula bajo Docencia

                DocenciaStudentDevelopmentSeeder::class,
                // Crea procesos de desarrollo estudiantil bajo Docencia

                // ─────────────────────────────────────────────────────────────
                // PASO 4: Procesos del Subsistema VINCULACIÓN (code 'V')
                // ─────────────────────────────────────────────────────────────
                VinculacionGestionConocimientoSeeder::class,
                // Crea:
                // - Categorías: GC, EC, CE, RD
                // - Proceso: Transferencia de Conocimiento y Tecnología (TCT)
                // - Subprocesos: TCT-01, TCT-02, TCT-03
                // - Documentos requeridos: PVV-02-F-001 a PVV-02-F-007

                // ─────────────────────────────────────────────────────────────
                // PASO 5: Metadatos de Documentos Específicos
                // ─────────────────────────────────────────────────────────────
                Pap01002MetadataSeeder::class,
                // Crea esquema de metadatos para PAP-01-F-002 (Docencia)

                VinculacionPvv02002MetadataSeeder::class,
                // Crea esquema de metadatos para PVV-02-F-002 (Vinculación)

                // ─────────────────────────────────────────────────────────────
                // PASO 6: Campos de Metadatos Genéricos
                // ─────────────────────────────────────────────────────────────
                UniversityMetadataFieldsSeeder::class,
                // Crea campos de metadatos reutilizables para toda la universidad
            ]);

            $this->command->info(" Seeders de producción ejecutados exitosamente");

        } else {
            // Entorno de desarrollo/testing - mismos seeders
            $this->command->info("  Ejecutando seeders en modo desarrollo");
            $this->command->info("  Para datos de testing específicos, ejecute: php artisan db:seed --class=TestingSeeder");
            $this->command->info("  Para datos de producción, ejecute: php artisan db:seed --class=ProductionSeeder");

            $this->call([
                BaseInstitutionSeeder::class,
                AdditionalDocumentTypesSeeder::class,
                AllSubsystemsSeeder::class,
                InstitutionGraduationSeeder::class,
                AdmissionMatriculaDocumentsSeeder::class,
                DocenciaStudentDevelopmentSeeder::class,
                VinculacionGestionConocimientoSeeder::class,
                Pap01002MetadataSeeder::class,
                VinculacionPvv02002MetadataSeeder::class,
                UniversityMetadataFieldsSeeder::class,
            ]);

            $this->command->info(" Seeders de desarrollo ejecutados exitosamente");
        }

        // =====================================================================
        // RESUMEN DE DATOS SEMBRADOS
        // =====================================================================
        $this->command->newLine();
        $this->command->info("📊 Resumen de datos sembrados:");
        $this->command->info("   • 1 Head Office (Sede Matriz Manta)");
        $this->command->info("   • 1 Department (FCVT)");
        $this->command->info("   • 9 Careers (AGR, IAGR, AGRN, AGRI, IAMB, TDI, SOFT, BIOL, ALIM)");
        $this->command->info("   • 4 Subsystems (Docencia, Investigación, Vinculación, Gestión)");
        $this->command->info("   • Procesos de Docencia (Graduación, Admisión, Desarrollo Estudiantil)");
        $this->command->info("   • Procesos de Vinculación (Gestión del Conocimiento)");
        $this->command->info("   • Metadatos para PAP-01-F-002 y PVV-02-F-002");
        $this->command->info("   • Campos de metadatos universitarios genéricos");
    }
}
