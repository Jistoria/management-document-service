<?php


return [
    // Prefijos que usa el auth-service según TokenType::getCachePrefix()
    // Ajusta estos valores para que coincidan con tu enum TokenType en auth-service
    'token_prefixes' => [
        'jwt:token',    // Azure/MS
        'local:token',  // Local/Passport
    ],

    // Claves opcionales por usuario que el auth-service persiste
    'user_keys' => [
        'jwt' => 'jwt:user:%s',
        'graph' => 'graph:user:%s',
        'local' => 'local:user:%s',
    ],

    // Scopes requeridos por este microservicio (opcional)
    'required_scopes' => [
        // 'documents.read',
        // 'documents.write',
    ],

    // Si quieres rechazar tokens expirados según 'exp' del payload cacheado
    'enforce_exp' => true,
];
