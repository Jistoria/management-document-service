<?php

namespace App\Constants;

/**
 * Entity types for metadata fields.
 *
 * Defines the different types of entities that metadata fields can reference.
 */
class EntityType
{
    public const USER = 1;
    public const FACULTY = 2;
    public const CAREER = 3;
    public const DEPARTMENT = 4;
    public const DOCUMENT = 5;
    public const PROCESS = 6;
    public const ACADEMIC_ROLE = 7;

    /**
     * Get all entity type IDs
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            self::USER,
            self::FACULTY,
            self::CAREER,
            self::DEPARTMENT,
            self::DOCUMENT,
            self::PROCESS,
            self::ACADEMIC_ROLE,
        ];
    }

    /**
     * Get label for entity type
     *
     * @param int $typeId
     * @return string
     */
    public static function getLabel(int $typeId): string
    {
        return match ($typeId) {
            self::USER => 'Usuario',
            self::FACULTY => 'Facultad',
            self::CAREER => 'Carrera',
            self::DEPARTMENT => 'Departamento',
            self::DOCUMENT => 'Documento',
            self::PROCESS => 'Proceso',
            self::ACADEMIC_ROLE => 'Rol Académico',
            default => 'Desconocido',
        };
    }

    /**
     * Get key for entity type
     *
     * @param int $typeId
     * @return string
     */
    public static function getKey(int $typeId): string
    {
        return match ($typeId) {
            self::USER => 'user',
            self::FACULTY => 'faculty',
            self::CAREER => 'career',
            self::DEPARTMENT => 'department',
            self::DOCUMENT => 'document',
            self::PROCESS => 'process',
            self::ACADEMIC_ROLE => 'academic_role',
            default => 'unknown',
        };
    }

    /**
     * Get all entity types as key-value pairs
     *
     * @return array ['key' => 'label']
     */
    public static function toArray(): array
    {
        return [
            'user' => 'Usuario',
            'faculty' => 'Facultad',
            'career' => 'Carrera',
            'department' => 'Departamento',
            'document' => 'Documento',
            'process' => 'Proceso',
            'academic_role' => 'Rol Académico',
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
