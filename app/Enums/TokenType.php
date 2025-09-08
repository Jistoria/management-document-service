<?php

namespace App\Enums;

enum TokenType: string
{
    case LOCAL = 'local';
    case AZURE = 'azure';

    public function getCachePrefix(): string
    {
        return match ($this) {
            self::LOCAL => 'local_token',
            self::AZURE => 'azure_token',
        };
    }

    public function getSessionPrefix(): string
    {
        return match ($this) {
            self::LOCAL => 'local:session',
            self::AZURE => 'azure:session',
        };
    }
}
