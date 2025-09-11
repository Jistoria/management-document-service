<?php

namespace App\Kafka\Contracts;

use App\Support\KafkaMessage;

interface MessageHandler
{
    public function topic(): string;
    public function handle(KafkaMessage $message): void;
}
