<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class AuthCacheReader
{
    public function __construct(
        private array $prefixes,
        private array $userKeys,
        private bool $enforceExp = true,
    ) {}

    public static function make(): self
    {
        $cfg = config('authcache');
        return new self(
            $cfg['token_prefixes'] ?? [],
            $cfg['user_keys'] ?? [],
            (bool)($cfg['enforce_exp'] ?? true),
        );
    }

    public function getTokenCache(string $token): ?array
    {
        $hash = hash('sha256', $token);

        foreach ($this->prefixes as $prefix) {
            $key = "{$prefix}:{$hash}";
            $raw = Redis::get($key);
            if (!$raw) {
                continue;
            }
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                continue;
            }

            // Chequear exp si aplica
            if ($this->enforceExp && isset($data['exp']) && time() >= (int)$data['exp']) {
                // token expirado en cache → se considera inválido
                continue;
            }

            // Adjuntar metadatos útiles
            $data['_cache_key']  = $key;
            $data['_token_hash'] = $hash;
            $data['_prefix']     = $prefix;
            return $data;
        }

        return null;
    }

    public function getUserSlices(string $userId): array
    {
        $out = [];
        foreach ($this->userKeys as $slice => $pattern) {
            $key = sprintf($pattern, $userId);
            $raw = Redis::get($key);
            if ($raw) {
                $decoded = json_decode($raw, true);
                $out[$slice] = is_array($decoded) ? $decoded : $raw;
                $out["_{$slice}_key"] = $key;
            }
        }
        return $out;
    }
}
