<?php
return [
    'brokers' => env('KAFKA_BROKERS', 'kafka:9092'),
    'consumer_groups' => [],
    'security_protocol' => 'PLAINTEXT',
    'sasl' => [],
    'debug' => false,
    'client_id' => env('KAFKA_CLIENT_ID','auth-service'),
];
