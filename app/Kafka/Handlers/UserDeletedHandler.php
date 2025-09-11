<?php

namespace App\Kafka\Handlers;

use App\Kafka\Contracts\MessageHandler;
use App\Kafka\Topics;
use App\Services\AuthProjectionService;
use App\Support\KafkaMessage;
use Illuminate\Support\Arr;

final class UserDeletedHandler implements MessageHandler
{
    public function __construct(private AuthProjectionService $projection) {}

    public function topic(): string
    {
        return Topics::USER_DELETED;
    }

    public function handle(KafkaMessage $message): void
    {
        if ($message->microserviceId() !== config('app.microservice_id')) return;

        $tenantId = $message->tenantId();
        $payload  = $message->payload();

        $this->projection->markUserDeleted(
            $tenantId,
            Arr::get($payload, 'after', []),
            Arr::get($payload, 'before', [])
        );
    }
}
