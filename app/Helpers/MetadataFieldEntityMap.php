<?php

namespace App\Helpers;

use App\Models\Career;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\HeadOffice;
use App\Models\Process;
use App\Models\ProcessCategory;
use App\Models\Subsystem;

/**
 * Helper for resolving entity references in metadata fields.
 *
 * Maps a short key to its corresponding model class and default
 * identifier column so metadata fields can reference other entities
 * consistently.
 */
class MetadataFieldEntityMap
{
    public const MAP = [
        'head_office' => ['model' => HeadOffice::class, 'column' => 'id'],
        'department' => ['model' => Department::class, 'column' => 'id'],
        'career' => ['model' => Career::class, 'column' => 'id'],
        'subsystem' => ['model' => Subsystem::class, 'column' => 'id'],
        'process_category' => ['model' => ProcessCategory::class, 'column' => 'id'],
        'process' => ['model' => Process::class, 'column' => 'id'],
        'document_type' => ['model' => DocumentType::class, 'column' => 'id'],
    ];

    /**
     * Get the model class for a given key.
     */
    public static function getModel(string $key): ?string
    {
        return self::MAP[$key]['model'] ?? null;
    }

    /**
     * Get the reference column for a given key.
     */
    public static function getColumn(string $key): ?string
    {
        return self::MAP[$key]['column'] ?? null;
    }

    /**
     * Determine if the provided key exists in the map.
     */
    public static function isValidKey(string $key): bool
    {
        return array_key_exists($key, self::MAP);
    }

    /**
     * Return all available keys.
     *
     * @return array<int,string>
     */
    public static function keys(): array
    {
        return array_keys(self::MAP);
    }

    /**
     * Get available keys as a comma separated string.
     */
    public static function keysString(): string
    {
        return implode(',', self::keys());
    }
}
