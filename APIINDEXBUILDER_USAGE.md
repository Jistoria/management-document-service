# ApiIndexBuilder Usage Guide

## Overview

The `ApiIndexBuilder` has been refactored to use a single `format` parameter to determine the response type, with a key-value closure strategy map for easy extension.

## Basic Usage

### 1. Standard Controller Integration

```php
<?php

namespace App\Http\Controllers;

use App\Helpers\ApiIndexBuilder;
use App\Http\Resources\YourResource;
use App\Services\YourService;
use Illuminate\Http\Request;
use function App\Helpers\catchSync;

class YourController extends Controller
{
    public function index(Request $request)
    {
        return catchSync(function () use ($request) {
            // Extract filters specific to your entity
            $filters = ApiIndexBuilder::extractFilters($request, [
                'specific_field1',
                'specific_field2'
            ]);

            return ApiIndexBuilder::build(
                $this->yourService,
                YourResource::class,
                $request,
                $filters
            );
        }, 'Data retrieved successfully');
    }
}
```

## API Usage Examples

### 1. Paginated Results

```bash
GET /api/departments?format=paginate&per_page=10
```

### 2. Minimal View

```bash
GET /api/departments?format=minimal
```

### 3. Dropdown Options

```bash
GET /api/departments?format=dropdown
```

### 4. Key-Value Pluck

```bash
GET /api/departments?format=pluck&pluck_key=id&pluck_label=name
```

### 5. Simple Collection (Default)

```bash
GET /api/departments?format=collection
# or just
GET /api/departments
```

## Legacy Support

The builder still supports legacy parameters for backward compatibility:

```bash
# These work but are deprecated
GET /api/departments?paginate=true
GET /api/departments?minimal=true
GET /api/departments?pluck=id&pluck_label=name
```

## Available Formats

-   `paginate`: Paginated results with pagination metadata
-   `minimal`: Minimal view with basic fields only
-   `dropdown`: Formatted for dropdown/select components
-   `pluck`: Key-value pairs for specific field extraction
-   `collection`: Simple collection (default)

## Custom Strategies

### 1. Register Custom Strategy

```php
<?php

// In a service provider or before using the builder
ApiIndexBuilder::registerCustomStrategy('csv', function ($ctx) {
    $collection = $ctx['service']->getAll($ctx['filters']);
    $data = $collection->map(fn($model) =>
        (new $ctx['resource']($model))->toArray($ctx['request'])
    );

    return [
        'type' => 'csv',
        'data' => $data,
        'headers' => $data->isNotEmpty() ? array_keys($data->first()) : [],
        'count' => $data->count(),
    ];
});
```

### 2. Use Custom Strategy

```bash
GET /api/departments?format=csv
```

### 3. Example Custom Strategies

```php
<?php

// Register multiple custom strategies
ApiIndexBuilder::registerExampleStrategies();

// Now you can use:
// GET /api/departments?format=stats
// GET /api/departments?format=tree
// GET /api/departments?format=csv
```

## Helper Methods

### 1. Extract Filters

```php
<?php

// Extract common filters plus entity-specific ones
$filters = ApiIndexBuilder::extractFilters($request, [
    'entity_specific_field1',
    'entity_specific_field2'
]);
```

### 2. Extract Includes

```php
<?php

$includes = ApiIndexBuilder::extractIncludes($request);
// From: /api/departments?include=head_office,careers
// Returns: ['head_office', 'careers']
```

### 3. Available Formats

```php
<?php

$formats = ApiIndexBuilder::getAvailableFormats();
// Returns: ['paginate', 'minimal', 'dropdown', 'pluck', 'collection']
```

### 4. Check Strategy Existence

```php
<?php

if (ApiIndexBuilder::hasStrategy('custom_format')) {
    // Strategy exists
}
```

## Resource Requirements

Your resource classes must implement these static methods:

```php
<?php

class YourResource extends BaseResource
{
    // Required by ApiIndexBuilder
    public static function paginated($paginator): array { ... }
    public static function simpleCollection($collection): array { ... }
    public static function forDropdown($collection): array { ... }
    public static function pluck($collection, string $key, string $label): array { ... }

    // Required by minimal format
    public function minimal(): array { ... }
}
```

## Service Requirements

Your service classes must implement these methods:

```php
<?php

class YourService
{
    public function getAll(array $filters = []): Collection { ... }
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator { ... }
}
```

## Benefits

1. **Single Parameter**: Use `format` instead of multiple boolean parameters
2. **Extensible**: Easy to add new formats via closures
3. **Consistent**: Same interface across all controllers
4. **Backwards Compatible**: Legacy parameters still work
5. **Type Safe**: Proper PHP type hints and documentation
6. **Testable**: Easy to mock and test strategies

## Migration Guide

### Before (Old Implementation)

```php
<?php

public function index(Request $request)
{
    $filters = [
        'search' => $request->input('search'),
        'code' => $request->input('code'),
    ];

    if ($request->boolean('paginate')) {
        $data = $this->service->getPaginated($request->input('per_page', 15), $filters);
        return YourResource::paginated($data);
    }

    if ($request->boolean('minimal')) {
        $data = $this->service->getAll($filters);
        return [
            'data' => $data->map(fn($item) => (new YourResource($item))->minimal()),
            'count' => $data->count(),
        ];
    }

    // ... more conditions
}
```

### After (New Implementation)

```php
<?php

public function index(Request $request)
{
    return catchSync(function () use ($request) {
        $filters = ApiIndexBuilder::extractFilters($request, ['entity_specific_field']);

        return ApiIndexBuilder::build(
            $this->service,
            YourResource::class,
            $request,
            $filters
        );
    }, 'Data retrieved successfully');
}
```

## Advanced Usage

### 1. Custom Strategy with Context

```php
<?php

ApiIndexBuilder::registerCustomStrategy('detailed', function ($ctx) {
    $collection = $ctx['service']->getAll($ctx['filters']);

    // Use context data
    $resource = $ctx['resource'];
    $request = $ctx['request'];
    $format = $ctx['format'];

    return [
        'type' => 'detailed',
        'data' => $collection->map(fn($model) => (new $resource($model))->detailed()),
        'meta' => [
            'requested_format' => $format,
            'filters_applied' => $ctx['filters'],
        ],
    ];
});
```

### 2. Override Default Strategies

```php
<?php

// Override the default paginate strategy
ApiIndexBuilder::registerCustomStrategy('paginate', function ($ctx) {
    // Your custom pagination logic
    return [
        'data' => [],
        'custom_pagination' => true,
    ];
});
```

This refactored `ApiIndexBuilder` provides a clean, extensible, and maintainable way to handle different response formats across your API controllers.
