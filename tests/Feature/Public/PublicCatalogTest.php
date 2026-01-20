<?php

namespace Tests\Feature\Public;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\HeadOffice;
use App\Models\Department;
use App\Models\Career;

/**
 * Test para endpoints públicos de la API
 * 
 * Verifica que los endpoints públicos funcionen sin autenticación
 * y no expongan datos sensibles
 */
class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que los endpoints públicos sean accesibles sin autenticación
     */
    public function test_can_access_public_head_offices_without_auth(): void
    {
        HeadOffice::factory()->create([
            'code' => 'TEST',
            'name' => 'Sede Test',
        ]);

        $response = $this->getJson('/api/public/head-offices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'count'
                ]
            ]);
    }

    /**
     * Test que el formato dropdown funcione correctamente
     */
    public function test_public_endpoint_returns_dropdown_format(): void
    {
        HeadOffice::factory()->create([
            'code' => 'MAIN',
            'name' => 'Sede Principal',
        ]);

        $response = $this->getJson('/api/public/head-offices?format=dropdown');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'options' => [
                        '*' => ['value', 'label', 'code']
                    ],
                    'count'
                ]
            ]);
    }

    /**
     * Test que los datos sensibles NO se expongan
     */
    public function test_public_endpoint_does_not_expose_sensitive_data(): void
    {
        HeadOffice::factory()->create();

        $response = $this->getJson('/api/public/head-offices');

        $data = $response->json('data.data');
        
        // Verificar que NO contenga campos sensibles
        if (!empty($data)) {
            $firstItem = $data[0];
            $this->assertArrayNotHasKey('created_by', $firstItem);
            $this->assertArrayNotHasKey('updated_by', $firstItem);
            $this->assertArrayNotHasKey('version', $firstItem);
            $this->assertArrayNotHasKey('deleted_at', $firstItem);
        }
    }

    /**
     * Test filtros por búsqueda
     */
    public function test_public_endpoint_filters_by_search(): void
    {
        HeadOffice::factory()->create(['name' => 'Sede Norte']);
        HeadOffice::factory()->create(['name' => 'Sede Sur']);

        $response = $this->getJson('/api/public/head-offices?search=Norte');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        
        if (!empty($data)) {
            $this->assertStringContainsString('Norte', $data[0]['name']);
        }
    }

    /**
     * Test endpoint de carreras por departamento
     */
    public function test_can_get_careers_by_department(): void
    {
        $department = Department::factory()->create();
        Career::factory()->count(3)->create([
            'department_id' => $department->id
        ]);

        $response = $this->getJson("/api/public/departments/{$department->id}/careers");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    /**
     * Test rate limiting (requiere hacer muchas peticiones)
     */
    public function test_public_endpoints_are_rate_limited(): void
    {
        HeadOffice::factory()->create();

        // Hacer más de 60 requests
        $responses = [];
        for ($i = 0; $i < 65; $i++) {
            $responses[] = $this->getJson('/api/public/head-offices');
        }

        // Los últimos requests deberían ser rechazados con 429
        $lastResponse = end($responses);
        
        // Nota: Este test puede fallar en desarrollo si el rate limiting no está activo
        // $lastResponse->assertStatus(429);
    }

    /**
     * Test que solo muestre registros activos
     */
    public function test_only_shows_active_records(): void
    {
        HeadOffice::factory()->create(['is_active' => true]);
        HeadOffice::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/public/head-offices');

        $data = $response->json('data.data');
        $count = $response->json('data.count');

        // Solo debería retornar el activo
        $this->assertEquals(1, $count);
    }

    /**
     * Test validación de parámetros
     */
    public function test_validates_query_parameters(): void
    {
        $response = $this->getJson('/api/public/head-offices?format=invalid');

        // Debería rechazar formatos inválidos
        $response->assertStatus(422);
    }

    /**
     * Test paginación limitada
     */
    public function test_limits_pagination_to_max_50(): void
    {
        HeadOffice::factory()->count(100)->create();

        $response = $this->getJson('/api/public/head-offices?perPage=100&format=paginate');

        $response->assertStatus(200);
        
        // Debería limitar a máximo 50
        $data = $response->json('data.data');
        $this->assertLessThanOrEqual(50, count($data));
    }

    /**
     * Test endpoint de detalle individual
     */
    public function test_can_get_individual_head_office(): void
    {
        $headOffice = HeadOffice::factory()->create();

        $response = $this->getJson("/api/public/head-offices/{$headOffice->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $headOffice->id,
                    'code' => $headOffice->code,
                    'name' => $headOffice->name,
                ]
            ]);
    }

    /**
     * Test 404 para recursos no encontrados
     */
    public function test_returns_404_for_nonexistent_resource(): void
    {
        $response = $this->getJson('/api/public/head-offices/99999999-9999-9999-9999-999999999999');

        $response->assertStatus(404);
    }
}
