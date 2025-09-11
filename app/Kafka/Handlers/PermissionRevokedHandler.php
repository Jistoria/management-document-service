<?php

namespace App\Kafka\Handlers;

use App\Kafka\Contracts\MessageHandler;
use App\Kafka\Topics;
use App\Services\AuthProjectionService;
use App\Support\KafkaMessage;
use Illuminate\Support\Arr;

final class PermissionRevokedHandler implements MessageHandler
{
    public function __construct(private AuthProjectionService $projection) {}

    public function topic(): string
    {
        return Topics::PERMISSION_REVOKED;
    }

    public function handle(KafkaMessage $message): void
    {
        if ($message->microserviceId() !== config('app.microservice_id')) return;

        $payload  = $message->payload();
        $tenantId = $message->tenantId();

        $user  = Arr::get($payload, 'user', []);
        $perms = Arr::get($payload, 'permissions', []);

        $this->projection->detachPermissions($tenantId, $user['id'] ?? null, $perms);
    }
}
