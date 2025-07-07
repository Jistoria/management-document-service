<?php

namespace App\Console\Commands;

use App\Models\HeadOffice;
use App\Models\Department;
use App\Models\Career;
use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestAuditSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:test
                            {--user-id= : ID del usuario para simular}
                            {--user-email= : Email del usuario para simular}
                            {--user-name= : Nombre del usuario para simular}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el sistema de auditoría creando, actualizando y eliminando registros';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔥 Iniciando prueba del sistema de auditoría...');

        // Simular contexto de usuario
        $this->simulateUserContext();

        // Test 1: Crear HeadOffice
        $this->info('📝 Test 1: Creando HeadOffice...');
        $headOffice = HeadOffice::create([
            'name' => 'Sede de Prueba Auditoría',
            'code' => 'SPA-' . rand(100, 999),
            'created_by' => $this->option('user-id') ?? 'test-user-123',
        ]);
        $this->info("✅ HeadOffice creado: {$headOffice->id}");

        // Test 2: Actualizar HeadOffice
        $this->info('📝 Test 2: Actualizando HeadOffice...');
        $headOffice->update([
            'name' => 'Sede de Prueba Auditoría ACTUALIZADA',
            'updated_by' => $this->option('user-id') ?? 'test-user-123',
        ]);
        $this->info("✅ HeadOffice actualizado");

        // Test 3: Crear Department
        $this->info('📝 Test 3: Creando Department...');
        $department = Department::create([
            'head_office_id' => $headOffice->id,
            'name' => 'Departamento de Prueba',
            'code' => 'DP-' . rand(100, 999),
            'created_by' => $this->option('user-id') ?? 'test-user-123',
        ]);
        $this->info("✅ Department creado: {$department->id}");

        // Test 4: Crear Career
        $this->info('📝 Test 4: Creando Career...');
        $career = Career::create([
            'department_id' => $department->id,
            'name' => 'Carrera de Prueba',
            'code' => 'CP-' . rand(100, 999),
            'created_by' => $this->option('user-id') ?? 'test-user-123',
        ]);
        $this->info("✅ Career creado: {$career->id}");

        // Test 5: Soft Delete
        $this->info('📝 Test 5: Soft Delete de Career...');
        $career->delete();
        $this->info("✅ Career eliminado (soft delete)");

        // Test 6: Restore
        $this->info('📝 Test 6: Restaurando Career...');
        $career->restore();
        $this->info("✅ Career restaurado");

        // Test 7: Auditoría manual
        $this->info('📝 Test 7: Auditoría manual...');
        $headOffice->auditManual('APPROVE', 'Aprobado por director en prueba', [
            'approval_level' => 'high',
            'approved_by_role' => 'director',
            'test_mode' => true,
        ]);
        $this->info("✅ Auditoría manual creada");

        // Mostrar resumen de auditoría
        $this->showAuditSummary($headOffice, $department, $career);

        // Limpiar datos de prueba
        if ($this->confirm('¿Deseas limpiar los datos de prueba?', true)) {
            $this->cleanupTestData($headOffice, $department, $career);
        }

        $this->info('🎉 Prueba del sistema de auditoría completada!');
    }

    /**
     * Simular contexto de usuario
     */
    protected function simulateUserContext(): void
    {
        // Simular headers de microservicio
        request()->headers->set('X-User-Id', $this->option('user-id') ?? 'test-user-123');
        request()->headers->set('X-User-Email', $this->option('user-email') ?? 'test@ejemplo.com');
        request()->headers->set('X-User-Name', $this->option('user-name') ?? 'Usuario de Prueba');
        request()->headers->set('X-Correlation-ID', 'test-audit-' . now()->format('YmdHis'));

        $this->info('👤 Contexto de usuario simulado:');
        $this->line('   - ID: ' . ($this->option('user-id') ?? 'test-user-123'));
        $this->line('   - Email: ' . ($this->option('user-email') ?? 'test@ejemplo.com'));
        $this->line('   - Nombre: ' . ($this->option('user-name') ?? 'Usuario de Prueba'));
    }

    /**
     * Mostrar resumen de auditoría
     */
    protected function showAuditSummary($headOffice, $department, $career): void
    {
        $this->info('📊 Resumen de auditoría generada:');

        // Auditoría de HeadOffice
        $headOfficeAudits = AuditLog::where('table_name', 'head_offices')
            ->where('record_id', $headOffice->id)
            ->get();

        $this->table(
            ['Tabla', 'Acción', 'Usuario', 'Fecha'],
            $headOfficeAudits->map(function ($audit) {
                return [
                    $audit->table_name,
                    $audit->action,
                    $audit->external_user_id ?? 'N/A',
                    $audit->created_at->format('Y-m-d H:i:s'),
                ];
            })
        );

        // Mostrar detalles de un cambio
        $updateAudit = $headOfficeAudits->where('action', 'UPDATE')->first();
        if ($updateAudit) {
            $this->info('🔍 Detalle del cambio UPDATE:');
            $this->line("   - Campos cambiados: " . implode(', ', $updateAudit->changed_fields));
            $this->line("   - Resumen: " . ($updateAudit->change_metadata['summary'] ?? 'N/A'));

            if (isset($updateAudit->change_metadata['field_details']['name'])) {
                $nameChange = $updateAudit->change_metadata['field_details']['name'];
                $this->line("   - Nombre: '{$nameChange['from']}' → '{$nameChange['to']}'");
            }
        }

        // Contar total de auditorías
        $totalAudits = AuditLog::whereIn('table_name', ['head_offices', 'departments', 'careers'])
            ->whereIn('record_id', [$headOffice->id, $department->id, $career->id])
            ->count();

        $this->info("📈 Total de registros de auditoría creados: {$totalAudits}");
    }

    /**
     * Limpiar datos de prueba
     */
    protected function cleanupTestData($headOffice, $department, $career): void
    {
        $this->info('🧹 Limpiando datos de prueba...');

        // Eliminar registros (esto también generará auditoría)
        $career->forceDelete();
        $department->forceDelete();
        $headOffice->forceDelete();

        // Eliminar registros de auditoría de prueba
        AuditLog::whereIn('table_name', ['head_offices', 'departments', 'careers'])
            ->whereIn('record_id', [$headOffice->id, $department->id, $career->id])
            ->delete();

        $this->info('✅ Datos de prueba limpiados');
    }
}
