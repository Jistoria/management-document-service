<?php

namespace App\Traits;

use Illuminate\Support\Str;
use InvalidArgumentException;

trait ValidatesUuid
{
    /**
     * Validate a single UUID
     */
    protected function validateUuid(string $uuid, string $fieldName = 'id'): void
    {
        if (!Str::isUuid($uuid)) {
            throw new InvalidArgumentException("The {$fieldName} must be a valid UUID.");
        }
    }

    /**
     * Validate an array of UUIDs
     */
    protected function validateUuidArray(array $uuids, string $fieldName = 'ids'): void
    {
        foreach ($uuids as $uuid) {
            if (!Str::isUuid($uuid)) {
                throw new InvalidArgumentException("One or more {$fieldName} are not valid UUIDs.");
            }
        }
    }
}
