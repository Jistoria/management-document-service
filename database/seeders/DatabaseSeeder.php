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

        if ($environment === 'production') {

            $this->call([
                InstitutionGraduationSeeder::class,
                AdmissionMatriculaDocumentsSeeder::class,
                DocenciaStudentDevelopmentSeeder::class,
                Pap01002MetadataSeeder::class,
            ]);
        } else {
            // Datos de prueba para desarrollo/testing
            $this->command->info("ℹ️ Para usar datos de testing, ejecute: php artisan db:seed --class=TestingSeeder");
            $this->command->info("ℹ️ Para usar datos de producción, ejecute: php artisan db:seed --class=ProductionSeeder");

            $this->call([
                InstitutionGraduationSeeder::class,
                AdmissionMatriculaDocumentsSeeder::class,
                DocenciaStudentDevelopmentSeeder::class,
                Pap01002MetadataSeeder::class,
            ]);
        }
    }
}
