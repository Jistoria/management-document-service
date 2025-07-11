# CamelCase to Snake_Case Mapping Implementation

## Overview

This document describes the implementation of automatic mapping between camelCase (frontend) and snake_case (database) attribute naming conventions in the Laravel application.

## Problem Statement

- **Frontend (JS/TS)**: Uses camelCase naming convention (`headOfficeId`, `departmentId`, `createdBy`)
- **Database/Models**: Uses snake_case naming convention (`head_office_id`, `department_id`, `created_by`)
- **Need**: Seamless conversion between both formats while maintaining backward compatibility

## Solution: HasCamelCaseAttributes Trait

### Key Features

1. **Automatic Conversion**: Converts camelCase to snake_case for database operations
2. **Bidirectional Mapping**: Supports both reading and writing in camelCase format
3. **Backward Compatibility**: Still works with snake_case inputs
4. **Mass Assignment Support**: Works with `fill()` and `create()` methods

### Implementation Details

#### Trait Location
`app/Traits/HasCamelCaseAttributes.php`

#### Key Methods

- `getAttribute($key)`: Converts camelCase reads to snake_case
- `setAttribute($key, $value)`: Converts camelCase writes to snake_case  
- `fill(array $attributes)`: Handles mass assignment with camelCase conversion
- `convertToSnakeCase(array $data)`: Static method to convert arrays
- `convertToCamelCase(array $data)`: Static method for reverse conversion

#### Mapping Configuration

```php
protected array $camelCaseMap = [
    'headOfficeId' => 'head_office_id',
    'departmentId' => 'department_id', 
    'createdBy' => 'created_by',
    'updatedBy' => 'updated_by',
    'deletedAt' => 'deleted_at',
    'createdAt' => 'created_at',
    'updatedAt' => 'updated_at',
];
```

### Updated Models

All main models now use the `HasCamelCaseAttributes` trait:

- `HeadOffice` model
- `Department` model  
- `Career` model

### Updated Services

Services now convert camelCase input to snake_case before database operations:

```php
// In CareerService::create()
$data = Career::convertToSnakeCase($data);

// In DepartmentService::update() 
$data = Department::convertToSnakeCase($data);
```

## Usage Examples

### Frontend (JavaScript/TypeScript)

```javascript
// Creating a department
const departmentData = {
  headOfficeId: "123e4567-e89b-12d3-a456-426614174000",
  name: "Engineering Department",
  code: "ENG",
  createdBy: "admin"
};

fetch('/api/departments', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(departmentData)
});
```

### Backend Processing

1. **Request Validation**: `StoreDepartmentRequest` receives camelCase
2. **Attribute Conversion**: `prepareForValidation()` creates snake_case copies
3. **Service Layer**: `convertToSnakeCase()` ensures database compatibility  
4. **Model Layer**: `HasCamelCaseAttributes` trait handles the mapping
5. **Database**: Stores in snake_case format

### API Response

Responses are converted to camelCase via the existing `CamelCaseResponse` trait and resources.

## Backward Compatibility

The system maintains full backward compatibility:

```php
// These all work:
$department->head_office_id;    // snake_case (legacy)
$department->headOfficeId;      // camelCase (new)

// Mass assignment supports both:
Department::create([
    'head_office_id' => '123',  // snake_case
    'name' => 'Test'
]);

Department::create([
    'headOfficeId' => '123',    // camelCase  
    'name' => 'Test'
]);
```

## Benefits

1. **Seamless Integration**: Frontend can use natural camelCase
2. **No Database Changes**: Existing snake_case schema unchanged
3. **Developer Experience**: Consistent naming across stack
4. **Maintainable**: Centralized mapping logic in trait
5. **Performance**: Minimal overhead during conversion

## Best Practices

1. **Always use camelCase** in new frontend implementations
2. **Update API documentation** to reflect camelCase parameters
3. **Add new mappings** to the trait when adding new camelCase fields
4. **Test both formats** to ensure backward compatibility

## Testing

Ensure tests cover both naming conventions:

```php
// Test camelCase input
$response = $this->postJson('/api/departments', [
    'headOfficeId' => $headOffice->id,
    'name' => 'Test Department'
]);

// Test snake_case input (backward compatibility)
$response = $this->postJson('/api/departments', [
    'head_office_id' => $headOffice->id,
    'name' => 'Test Department'  
]);
```

## Migration Strategy

1. ✅ **Phase 1**: Implement trait and update models
2. ✅ **Phase 2**: Update services to use conversion methods
3. ✅ **Phase 3**: Update request classes for camelCase validation
4. ✅ **Phase 4**: Update API documentation
5. 🔄 **Phase 5**: Update frontend applications to use camelCase
6. 📋 **Phase 6**: Eventually deprecate snake_case support (future)

## Monitoring

Monitor API usage to track adoption of camelCase vs snake_case parameters to plan future deprecation of snake_case support.
