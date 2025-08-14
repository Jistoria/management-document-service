<?php

namespace App\Constants;

/**
 * Supported data types for metadata fields.
 *
 * Provides normalization and validation helpers for values
 * that will be consumed by other services.
 */
class MetadataFieldDataType
{
    public const STRING = 'string';
    public const INTEGER = 'integer';
    public const DECIMAL = 'decimal';
    public const DATE = 'date';
    public const BOOLEAN = 'boolean';
    public const JSON = 'json';
    public const UUID = 'uuid';
    public const TEXT = 'text';
    public const EMAIL = 'email';
    public const URL = 'url';

    public const ALL = [
        self::STRING,
        self::INTEGER,
        self::DECIMAL,
        self::DATE,
        self::BOOLEAN,
        self::JSON,
        self::UUID,
        self::TEXT,
        self::EMAIL,
        self::URL,
    ];

    /**
     * Normalize common aliases to canonical types.
     *
     * @param string $type Raw type value
     * @return string Canonical data type
     */
    public static function normalize(string $type): string
    {
        $type = strtolower(trim($type));
        $aliases = [
            'int' => self::INTEGER,
            'float' => self::DECIMAL,
            'double' => self::DECIMAL,
            'bool' => self::BOOLEAN,
        ];
        return $aliases[$type] ?? $type;
    }

    /**
     * Determine if the given type is supported.
     *
     * @param string $type Raw type value
     * @return bool Whether the type is valid
     */
    public static function isValid(string $type): bool
    {
        return in_array(self::normalize($type), self::ALL, true);
    }
}
