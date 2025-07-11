<?php

namespace App\Helpers;

use Illuminate\Support\Str;

/**
 * Helper class for converting between snake_case and camelCase
 *
 * This ensures consistent API responses for JavaScript/TypeScript frontends
 */
class CaseConverter
{
    /**
     * Convert array keys from snake_case to camelCase recursively
     *
     * @param array|object $data
     * @return array
     */
    public static function snakeToCamel($data): array
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            return $data;
        }

        $converted = [];

        foreach ($data as $key => $value) {
            // Convert key to camelCase
            $camelKey = Str::camel($key);

            // Recursively convert nested arrays/objects
            if (is_array($value) || is_object($value)) {
                $converted[$camelKey] = self::snakeToCamel($value);
            } else {
                $converted[$camelKey] = $value;
            }
        }

        return $converted;
    }

    /**
     * Convert array keys from camelCase to snake_case recursively
     *
     * @param array|object $data
     * @return array
     */
    public static function camelToSnake($data): array
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            return $data;
        }

        $converted = [];

        foreach ($data as $key => $value) {
            // Convert key to snake_case
            $snakeKey = Str::snake($key);

            // Recursively convert nested arrays/objects
            if (is_array($value) || is_object($value)) {
                $converted[$snakeKey] = self::camelToSnake($value);
            } else {
                $converted[$snakeKey] = $value;
            }
        }

        return $converted;
    }

    /**
     * Convert paginated data structure to camelCase
     *
     * @param array $paginatedData
     * @return array
     */
    public static function convertPaginatedResponse(array $paginatedData): array
    {
        $converted = [];

        foreach ($paginatedData as $key => $value) {
            if ($key === 'data' && is_array($value)) {
                // Convert each item in the data array
                $converted['data'] = array_map([self::class, 'snakeToCamel'], $value);
            } elseif ($key === 'pagination' && is_array($value)) {
                // Convert pagination metadata
                $converted['pagination'] = self::snakeToCamel($value);
            } else {
                // Convert other top-level keys
                $converted[Str::camel($key)] = is_array($value) ? self::snakeToCamel($value) : $value;
            }
        }

        return $converted;
    }

    /**
     * Convert bulk operation response to camelCase
     *
     * @param array $bulkResponse
     * @return array
     */
    public static function convertBulkResponse(array $bulkResponse): array
    {
        return self::snakeToCamel($bulkResponse);
    }

    /**
     * Convert error response to camelCase
     *
     * @param array $errorResponse
     * @return array
     */
    public static function convertErrorResponse(array $errorResponse): array
    {
        return self::snakeToCamel($errorResponse);
    }

    /**
     * List of common snake_case to camelCase mappings for faster conversion
     *
     * @return array
     */
    public static function getCommonMappings(): array
    {
        return [
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            'deleted_at' => 'deletedAt',
            'created_by' => 'createdBy',
            'updated_by' => 'updatedBy',
            'head_office_id' => 'headOfficeId',
            'department_id' => 'departmentId',
            'process_id' => 'processId',
            'document_type_id' => 'documentTypeId',
            'metadata_schema_id' => 'metadataSchemaId',
            'storage_unit_id' => 'storageUnitId',
            'current_page' => 'currentPage',
            'last_page' => 'lastPage',
            'per_page' => 'perPage',
            'has_more_pages' => 'hasMorePages',
            'departments_count' => 'departmentsCount',
            'careers_count' => 'careersCount',
            'has_departments' => 'hasDepartments',
            'has_careers' => 'hasCareers',
            'head_office_name' => 'headOfficeName',
            'department_name' => 'departmentName',
            'resource_type' => 'resourceType',
            'generated_at' => 'generatedAt',
            'deleted_count' => 'deletedCount',
            'total_requested' => 'totalRequested',
            'success_rate' => 'successRate',
        ];
    }
}
