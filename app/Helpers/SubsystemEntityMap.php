<?php

namespace App\Helpers;

use App\Models\Career;
use App\Models\Department;
use App\Models\HeadOffice;

/**
 * Map de entities disponibles para ser vinculadas a un Subsystem.
 */
class SubsystemEntityMap
{
    public const MAP = [
        'career' => ['table' => 'careers', 'model' => Career::class],
        'department' => ['table' => 'departments', 'model' => Department::class],
        'head_office' => ['table' => 'head_offices', 'model' => HeadOffice::class],
    ];

    public static function getTable(string $key): ?string
    {
        return self::MAP[$key]['table'] ?? null;
    }

    public static function getModel(string $key): ?string
    {
        return self::MAP[$key]['model'] ?? null;
    }

    public static function isValidKey(string $key): bool
    {
        return array_key_exists($key, self::MAP);
    }

    public static function keys(): array
    {
        return array_keys(self::MAP);
    }

    public  static function keysString() : string
    {
        return implode(',', array_keys(self::MAP));
    }
}
