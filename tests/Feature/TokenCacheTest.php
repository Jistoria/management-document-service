<?php

namespace Tests\Feature;

use App\Jobs\CacheTokenValidation;
use App\Services\TokenValidationService;
use App\Services\AzureJwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;
use Mockery;

class TokenCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Limpiar cache antes de cada test
        $patterns = ['jwt:validated:*', 'jwt:user:*', 'graph:user:*'];
        foreach ($patterns as $pattern) {
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                Redis::del(...$keys);
            }
        }
    }

    public function test_token_validation_service_caches_valid_tokens()
    {
        // Mock del AzureJwt service
        $azureJwtMock = Mockery::mock(AzureJwt::class);
        $azureJwtMock->shouldReceive('validate')
            ->once()
            ->with('fake.jwt.token', [])
            ->andReturn([
                'oid' => 'test-user-id',
                'preferred_username' => 'test@example.com',
                'scp' => ['User.Read'],
                'exp' => time() + 3600,
            ]);

        $this->app->instance(AzureJwt::class, $azureJwtMock);

        $tokenValidationService = app(TokenValidationService::class);

        // Primera validación - debe llamar al service real
        $result1 = $tokenValidationService->validateToken('fake.jwt.token');

        $this->assertEquals('test-user-id', $result1['user_id']);
        $this->assertEquals('realtime', $result1['source']);

        // Segunda validación - debe usar cache
        $result2 = $tokenValidationService->validateToken('fake.jwt.token');

        $this->assertEquals('test-user-id', $result2['user_id']);
        // No debe llamar de nuevo al AzureJwt service
    }

    public function test_token_validation_queues_async_validation()
    {
        Queue::fake();

        $azureJwtMock = Mockery::mock(AzureJwt::class);
        $azureJwtMock->shouldReceive('validate')
            ->once()
            ->andReturn([
                'oid' => 'test-user-id',
                'preferred_username' => 'test@example.com',
                'scp' => ['User.Read'],
                'exp' => time() + 3600,
            ]);

        $this->app->instance(AzureJwt::class, $azureJwtMock);

        $tokenValidationService = app(TokenValidationService::class);
        $tokenValidationService->validateToken('fake.jwt.token');

        // Verificar que se programó el job asíncrono
        Queue::assertPushed(CacheTokenValidation::class, function ($job) {
            return $job->queue === 'auth-cache';
        });
    }

    public function test_cache_invalidation_works()
    {
        $azureJwtMock = Mockery::mock(AzureJwt::class);
        $azureJwtMock->shouldReceive('validate')
            ->twice() // Se llamará dos veces: antes y después de invalidar
            ->andReturn([
                'oid' => 'test-user-id',
                'preferred_username' => 'test@example.com',
                'scp' => ['User.Read'],
                'exp' => time() + 3600,
            ]);

        $this->app->instance(AzureJwt::class, $azureJwtMock);

        $tokenValidationService = app(TokenValidationService::class);

        // Validar y cachear
        $tokenValidationService->validateToken('fake.jwt.token');

        // Verificar que está en cache
        $this->assertTrue($tokenValidationService->isTokenPreValidated('fake.jwt.token'));

        // Invalidar cache
        $tokenValidationService->invalidateToken('fake.jwt.token');

        // Verificar que ya no está en cache
        $this->assertFalse($tokenValidationService->isTokenPreValidated('fake.jwt.token'));

        // Validar de nuevo - debe llamar al service real otra vez
        $tokenValidationService->validateToken('fake.jwt.token');
    }

    public function test_preload_token_validation_schedules_job()
    {
        Queue::fake();

        $tokenValidationService = app(TokenValidationService::class);
        $tokenValidationService->preloadTokenValidation('fake.jwt.token', ['User.Read']);

        Queue::assertPushed(CacheTokenValidation::class, function ($job) {
            return $job->queue === 'auth-cache';
        });
    }

    public function test_cache_stats_returns_correct_data()
    {
        $azureJwtMock = Mockery::mock(AzureJwt::class);
        $azureJwtMock->shouldReceive('validate')
            ->once()
            ->andReturn([
                'oid' => 'test-user-id',
                'preferred_username' => 'test@example.com',
                'scp' => ['User.Read'],
                'exp' => time() + 3600,
            ]);

        $this->app->instance(AzureJwt::class, $azureJwtMock);

        $tokenValidationService = app(TokenValidationService::class);

        // Validar un token para crear cache
        $tokenValidationService->validateToken('fake.jwt.token');

        $stats = $tokenValidationService->getCacheStats();

        $this->assertGreaterThan(0, $stats['total_cached_tokens']);
        $this->assertArrayHasKey('average_ttl', $stats);
    }

    public function test_insufficient_scopes_are_handled()
    {
        $azureJwtMock = Mockery::mock(AzureJwt::class);
        $azureJwtMock->shouldReceive('validate')
            ->once()
            ->with('fake.jwt.token', ['Admin.Read'])
            ->andThrow(new \RuntimeException('Insufficient scope'));

        $this->app->instance(AzureJwt::class, $azureJwtMock);

        $tokenValidationService = app(TokenValidationService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Insufficient scope');

        $tokenValidationService->validateToken('fake.jwt.token', ['Admin.Read']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}