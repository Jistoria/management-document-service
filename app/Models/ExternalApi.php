<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model for External APIs
 *
 * Represents configuration for external APIs used by the microservice.
 * Manages authentication, timeouts, and service integration settings.
 *
 * @property string $id
 * @property string $service_name
 * @property string $base_url
 * @property string $auth_method
 * @property int $timeout_seconds
 * @property int $retry_attempts
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $created_by
 * @property string|null $updated_by
 */
class ExternalApi extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'external_apis';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'service_name',
        'base_url',
        'auth_method',
        'timeout_seconds',
        'retry_attempts',
        'is_active',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'timeout_seconds' => 'integer',
        'retry_attempts' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Available authentication methods.
     */
    const AUTH_METHODS = [
        'bearer' => 'Bearer Token',
        'basic' => 'Basic Authentication',
        'api_key' => 'API Key',
        'oauth2' => 'OAuth 2.0'
    ];

    /**
     * Common service names.
     */
    const COMMON_SERVICES = [
        'auth-service' => 'Authentication Service',
        'user-service' => 'User Management Service',
        'file-storage-service' => 'File Storage Service',
        'notification-service' => 'Notification Service',
        'document-processing-service' => 'Document Processing Service',
        'audit-service' => 'Audit Service'
    ];

    /**
     * Scope to filter by service name.
     */
    public function scopeByServiceName($query, string $serviceName)
    {
        return $query->where('service_name', $serviceName);
    }

    /**
     * Scope to filter by authentication method.
     */
    public function scopeByAuthMethod($query, string $authMethod)
    {
        return $query->where('auth_method', $authMethod);
    }

    /**
     * Scope to get active APIs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get inactive APIs.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the display name for the authentication method.
     */
    public function getAuthMethodDisplayName(): string
    {
        return self::AUTH_METHODS[$this->auth_method] ?? $this->auth_method;
    }

    /**
     * Get the service display name.
     */
    public function getServiceDisplayName(): string
    {
        return self::COMMON_SERVICES[$this->service_name] ?? $this->service_name;
    }

    /**
     * Build the full URL for an endpoint.
     */
    public function buildUrl(string $endpoint): string
    {
        $baseUrl = rtrim($this->base_url, '/');
        $endpoint = ltrim($endpoint, '/');

        return $baseUrl . '/' . $endpoint;
    }

    /**
     * Get HTTP client configuration array.
     */
    public function getHttpClientConfig(): array
    {
        return [
            'base_uri' => $this->base_url,
            'timeout' => $this->timeout_seconds,
            'verify' => true, // Enable SSL verification
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => 'Management-Document-Service/1.0'
            ]
        ];
    }

    /**
     * Get authentication headers based on auth method.
     */
    public function getAuthHeaders(?string $token = null): array
    {
        if (!$token) {
            return [];
        }

        switch ($this->auth_method) {
            case 'bearer':
                return ['Authorization' => 'Bearer ' . $token];
            case 'basic':
                return ['Authorization' => 'Basic ' . base64_encode($token)];
            case 'api_key':
                return ['X-API-Key' => $token];
            case 'oauth2':
                return ['Authorization' => 'Bearer ' . $token];
            default:
                return [];
        }
    }

    /**
     * Check if the service is available for requests.
     */
    public function isAvailable(): bool
    {
        return $this->is_active && !empty($this->base_url);
    }

    /**
     * Get retry configuration.
     */
    public function getRetryConfig(): array
    {
        return [
            'attempts' => $this->retry_attempts,
            'delay' => 1000, // 1 second delay between retries
            'multiplier' => 2, // Exponential backoff
            'max_delay' => 10000 // Max 10 seconds delay
        ];
    }

    /**
     * Activate the API service.
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the API service.
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Test the API connection.
     */
    public function testConnection(): bool
    {
        try {
            // This would typically make a health check request
            // Implementation depends on your HTTP client choice
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
