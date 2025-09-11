<?php
return [
    // brokers, group, etc.
    'brokers' => env('KAFKA_BROKERS', 'kafka:9092'),
    'consumer_group_id' => env('AUTH_SYNC_GROUP', 'mgmt-docs-auth-sync'),

    // 👇 clave: valor booleano (no null)
    'auto_commit' => (bool) env('KAFKA_AUTO_COMMIT', false),

    // (opcional según tu versión)
    'security_protocol' => env('KAFKA_SECURITY_PROTOCOL', 'PLAINTEXT'),
    'sasl' => [
        'mechanisms' => env('KAFKA_SASL_MECHANISMS'),
        'username'   => env('KAFKA_SASL_USERNAME'),
        'password'   => env('KAFKA_SASL_PASSWORD'),
    ],

    // otros flags que tu versión soporte...
];
