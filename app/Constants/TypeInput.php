<?php

namespace App\Constants;

/**
 * Input types for metadata fields.
 *
 * Defines the conceptual type of input that determines how the field
 * should be interpreted and what kind of value it stores.
 *
 * - DOCUMENT: Simple field that stores a plain value (no reference)
 * - ENTITY: Field that references an entity object (faculty, career, department, etc.)
 * - PERSON: Field that references a user/person object
 */
class TypeInput
{
    public const DOCUMENT = 1;
    public const ENTITY = 2;
    public const PERSON = 3;

    /**
     * Get all type input IDs
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            self::DOCUMENT,
            self::ENTITY,
            self::PERSON,
        ];
    }

    /**
     * Get label for type input
     *
     * @param int $typeId
     * @return string
     */
    public static function getLabel(int $typeId): string
    {
        return match ($typeId) {
            self::DOCUMENT => 'Documental',
            self::ENTITY => 'Entidad',
            self::PERSON => 'Persona',
            default => 'Desconocido',
        };
    }

    /**
     * Get key for type input
     *
     * @param int $typeId
     * @return string
     */
    public static function getKey(int $typeId): string
    {
        return match ($typeId) {
            self::DOCUMENT => 'document',
            self::ENTITY => 'entity',
            self::PERSON => 'person',
            default => 'unknown',
        };
    }

    /**
     * Get all type inputs as key-value pairs
     *
     * @return array ['key' => 'label']
     */
    public static function toArray(): array
    {
        return [
            'document' => 'Documental',
            'entity' => 'Entidad',
            'person' => 'Persona',
        ];
    }

    /**
     * Validate if the given ID is valid
     *
     * @param int $typeId
     * @return bool
     */
    public static function isValid(int $typeId): bool
    {
        return in_array($typeId, self::all(), true);
    }
}
