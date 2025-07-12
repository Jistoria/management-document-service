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
        // Determinar el entorno y ejecutar el seeder correspondiente
        $environment = app()->environment();

        $this->command->info("🌍 Ejecutando seeders para entorno: {$environment}");

        if ($environment === 'production') {
            // Datos base para producción
            $this->call([
                ProductionSeeder::class,
            ]);
        } else {
            // Datos de prueba para desarrollo/testing
            $this->command->info("ℹ️ Para usar datos de testing, ejecute: php artisan db:seed --class=TestingSeeder");
            $this->command->info("ℹ️ Para usar datos de producción, ejecute: php artisan db:seed --class=ProductionSeeder");

            // Por defecto, usar el seeder de producción para tener datos base
            $this->call([
                ProductionSeeder::class,
            ]);
        }
    }
}
