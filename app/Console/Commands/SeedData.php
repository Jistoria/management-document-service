<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:data {type=production : The type of seeder to run (production|testing|both)}
                            {--fresh : Refresh the database before seeding}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecutar seeders específicos para poblar la base de datos con datos de producción o testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $fresh = $this->option('fresh');
        $force = $this->option('force');

        // Verificar entorno de producción
        if (app()->environment('production') && !$force && $type === 'testing') {
            $this->error('❌ No se puede ejecutar el seeder de testing en producción sin --force');
            return Command::FAILURE;
        }

        // Confirmar en producción
        if (app()->environment('production') && $type === 'testing' && $force) {
            if (!$this->confirm('⚠️ ¿Estás seguro de ejecutar el seeder de testing en producción?')) {
                $this->info('Operación cancelada');
                return Command::SUCCESS;
            }
        }

        // Refrescar base de datos si se solicita
        if ($fresh) {
            if (app()->environment('production') && !$this->confirm('⚠️ ¿Refrescar la base de datos en producción?')) {
                $this->info('Operación cancelada');
                return Command::SUCCESS;
            }

            $this->info('🔄 Refrescando base de datos...');
            Artisan::call('migrate:refresh', [], $this->getOutput());
            $this->info(' Base de datos refrescada');
        }

        // Ejecutar seeders según el tipo
        switch ($type) {
            case 'production':
                $this->runProductionSeeder();
                break;

            case 'testing':
                $this->runTestingSeeder();
                break;

            case 'both':
                $this->runProductionSeeder();
                $this->runTestingSeeder();
                break;

            default:
                $this->error("❌ Tipo de seeder inválido: {$type}");
                $this->info('Tipos válidos: production, testing, both');
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Run the production seeder
     */
    private function runProductionSeeder(): void
    {
        $this->info('🏭 Ejecutando ProductionSeeder...');

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'ProductionSeeder'
        ], $this->getOutput());

        if ($exitCode === 0) {
            $this->info(' ProductionSeeder completado exitosamente');
        } else {
            $this->error('❌ Error ejecutando ProductionSeeder');
        }
    }

    /**
     * Run the testing seeder
     */
    private function runTestingSeeder(): void
    {
        $this->info('🧪 Ejecutando TestingSeeder...');

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'TestingSeeder'
        ], $this->getOutput());

        if ($exitCode === 0) {
            $this->info(' TestingSeeder completado exitosamente');
        } else {
            $this->error('❌ Error ejecutando TestingSeeder');
        }
    }
}
