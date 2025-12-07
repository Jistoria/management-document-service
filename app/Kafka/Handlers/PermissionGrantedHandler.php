<?php

namespace App\Kafka\Handlers;

use App\Kafka\Contracts\MessageHandler;
use App\Kafka\Topics;
use App\Services\AuthProjectionService;
use App\Support\KafkaMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

final class PermissionGrantedHandler implements MessageHandler
{
    public function __construct(private AuthProjectionService $projection) {}

    public function topic(): string
    {
        return Topics::PERMISSION_GRANTED;
    }

    public function handle(KafkaMessage $message): void
    {
        Log::info('Permiso concedido - Procesando mensaje', ['payload' => $message->payload(), 'topic' => $this->topic(), 'local_microservice_id' => config('app.microservice_id'), 'message_microservice_id' => $message->microserviceId()]);

        if ($message->microserviceId() !== config('app.microservice_id')) return;

        $payload  = $message->payload();
        $tenantId = $message->tenantId();
        // Fallback para el ID
        $aggregateId = $message->aggregateId(); 

        $user  = Arr::get($payload, 'user', []);
        $perms = Arr::get($payload, 'permissions', []);
        $by    = Arr::get($payload, 'grantedBy');
        $why   = Arr::get($payload, 'reason');

        // Asegurar ID de usuario
        $userId = $user['id'] ?? $aggregateId;

        // Proyectar usuario (si viene info)
        if (!empty($user)) {
            // Inyectar ID si falta
            if (empty($user['id'])) $user['id'] = $userId;
            $this->projection->upsertUserFromSnapshot($tenantId, $user);
        }

        // Proyectar permisos
        $this->projection->attachPermissions($tenantId, $userId, $perms, $by, $why);
    }
}
