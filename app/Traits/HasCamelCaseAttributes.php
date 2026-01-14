<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait to handle camelCase attribute mapping to snake_case database fields
 */
trait HasCamelCaseAttributes
{
    /**
     * camelCase to snake_case attribute mapping
     */
    protected array $camelCaseMap = [
        'headOfficeId' => 'head_office_id',
        'departmentId' => 'department_id',
        'storageUnitTypeId' => 'storage_unit_type_id',
        'parentId' => 'parent_id',
        'canHaveChildren' => 'can_have_children',
        'templatePath' => 'template_path',
        'templateFilename' => 'template_filename',
        'codeDefault' => 'code_default',
        'urlResource' => 'url_resource',
        'isPublic' => 'is_public',  
        'createdBy' => 'created_by',
        'updatedBy' => 'updated_by',
        'deletedAt' => 'deleted_at',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    /**
     * Override getAttribute to support camelCase access
     */
    public function getAttribute($key)
    {
        // If it's a camelCase key, convert to snake_case
        if (isset($this->camelCaseMap[$key])) {
            $key = $this->camelCaseMap[$key];
        }

        return parent::getAttribute($key);
    }

    /**
     * Override setAttribute to support camelCase assignment
     */
    public function setAttribute($key, $value)
    {
        // If it's a camelCase key, convert to snake_case
        if (isset($this->camelCaseMap[$key])) {
            $key = $this->camelCaseMap[$key];
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Override fill to support camelCase mass assignment
     */
    public function fill(array $attributes)
    {
        $mappedAttributes = [];

        foreach ($attributes as $key => $value) {
            // Convert camelCase to snake_case if mapping exists
            $mappedKey = $this->camelCaseMap[$key] ?? $key;
            $mappedAttributes[$mappedKey] = $value;
        }

        return parent::fill($mappedAttributes);
    }

    /**
     * Get the camelCase version of snake_case attributes
     */
    public function getCamelCaseAttributes(): array
    {
        $attributes = $this->getAttributes();
        $camelCaseAttributes = [];

        // Reverse mapping: snake_case to camelCase
        $reverseMap = array_flip($this->camelCaseMap);

        foreach ($attributes as $key => $value) {
            $camelKey = $reverseMap[$key] ?? Str::camel($key);
            $camelCaseAttributes[$camelKey] = $value;
        }

        return $camelCaseAttributes;
    }

    /**
     * Convert snake_case array to camelCase
     */
    public static function convertToCamelCase(array $data): array
    {
        $converted = [];
        $instance = new static;
        $reverseMap = array_flip($instance->camelCaseMap ?? []);

        foreach ($data as $key => $value) {
            $camelKey = $reverseMap[$key] ?? Str::camel($key);
            $converted[$camelKey] = $value;
        }

        return $converted;
    }

    /**
     * Convert camelCase array to snake_case
     */
    public static function convertToSnakeCase(array $data): array
    {
        $converted = [];
        $instance = new static;
        $map = $instance->camelCaseMap ?? [];

        foreach ($data as $key => $value) {
            $snakeKey = $map[$key] ?? Str::snake($key);
            $converted[$snakeKey] = $value;
        }

        return $converted;
    }
}
