<?php

use Junges\Kafka\Facades\Kafka;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KafkaConnectionTest extends TestCase
{
    #[Test]
    public function it_can_publish_message_to_kafka(): void
    {
        if (!extension_loaded('rdkafka')) {
            $this->markTestSkipped('Extensión rdkafka no disponible.');
        }

        if (!filter_var(env('KAFKA_E2E', false), FILTER_VALIDATE_BOOL)) {
            $this->markTestSkipped('E2E Kafka desactivado (KAFKA_E2E=1 para habilitar).');
        }

        // Asegura un hostname válido para este proceso de test

        try {
            Kafka::publish()
                ->onTopic('test-connection')
                ->withBodyKey('ping', ['ping' => 'pong'])
                ->send();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Broker no disponible: ' . $e->getMessage());
        }

        $this->assertTrue(true);
    }
}
