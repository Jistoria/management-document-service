<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Junges\Kafka\Contracts\ConsumerMessage;

final class KafkaMessage
{
    public function __construct(private ConsumerMessage $msg) {}

    public function topic(): string
    {
        return $this->msg->getTopicName();
    }
    public function partition(): int
    {
        return $this->msg->getPartition();
    }
    public function offset(): int
    {
        return $this->msg->getOffset();
    }
    public function key(): ?string
    {
        return $this->msg->getKey();
    }

    /** @return array<string,mixed> */
    public function headers(): array
    {
        $headers = $this->msg->getHeaders() ?? [];
        // Normaliza a lower-case para fácil acceso
        return collect($headers)->mapWithKeys(fn($v, $k) => [strtolower($k) => $v])->all();
    }

    /** @return array<string,mixed> */
    public function payload(): array
    {
        $body = $this->msg->getBody();
        if (is_array($body)) return $body;

        $json = is_string($body) ? json_decode($body, true) : [];
        return is_array($json) ? $json : [];
    }

    public function tenantId(): ?string
    {
        return Arr::get($this->headers(), 'x-tenant-id');
    }

    public function microserviceId(): ?string
    {
        return Arr::get($this->headers(), 'x-microservice-id');
    }

    public function aggregateId(): ?string
    {
        return Arr::get($this->headers(), 'x-aggregate-id');
    }
}
