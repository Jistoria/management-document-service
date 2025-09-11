<?php

namespace App\Kafka\Handlers;

use App\Kafka\Contracts\MessageHandler;
use App\Kafka\Topics;
use App\Services\AuthProjectionService;
use App\Support\KafkaMessage;
use Illuminate\Support\Arr;

final class PermissionGrantedHandler implements MessageHandler
{
    public function __construct(private AuthProjectionService $projection) {}

    public function topic(): string
    {
        return Topics::PERMISSION_GRANTED;
    }

    public function handle(KafkaMessage $message): void
    {
        // Filtro por microservicio (defensa en profundidad)
        if ($message->microserviceId() !== config('app.microservice_id')) return;

        $payload  = $message->payload();
        $tenantId = $message->tenantId();

        $user  = Arr::get($payload, 'user', []);
        $perms = Arr::get($payload, 'permissions', []);
        $by    = Arr::get($payload, 'grantedBy');
        $why   = Arr::get($payload, 'reason');

        $this->projection->upsertUserFromSnapshot($tenantId, $user);
        $this->projection->attachPermissions($tenantId, $user['id'] ?? null, $perms, $by, $why);
    }
}
