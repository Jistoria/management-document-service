# Career CRUD Implementation

## Overview

Complete CRUD implementation for Career entity following the established patterns with Department and HeadOffice controllers.

## Components Created

### 1. CareerService

**Path**: `app/Services/CareerService.php`

**Features**:

-   Complete CRUD operations
-   Filtering and pagination support
-   Relationship management (Department, HeadOffice, Subsystems)
-   Business validation
-   Bulk operations
-   Statistics and hierarchy methods
-   Optimistic locking with version control

**Key Methods**:

-   `getAll(array $filters = [])`: Get all careers with filters
-   `getPaginated(int $perPage = 15, array $filters = [])`: Paginated results
-   `findById(string $id)`: Find by ID with relationships
-   `findByCode(string $code)`: Find by unique code
-   `create(array $data)`: Create new career with validation
-   `update(string $id, array $data)`: Update with version control
-   `delete(string $id)`: Soft delete with subsystem validation
-   `restore(string $id)`: Restore soft-deleted career
-   `getStatistics(string $id)`: Career statistics
-   `getFullHierarchy(string $id)`: Complete hierarchy data
-   `bulkDelete(array $ids)`: Bulk delete operation
-   `getByDepartment(string $departmentId, array $filters = [])`: Filter by department

### 2. CareerController

**Path**: `app/Http/Controllers/CareerController.php`

**Features**:

-   Full REST API implementation
-   OpenAPI documentation
-   ApiIndexBuilder integration
-   Relationship loading support
-   Error handling with catchSync
-   Bulk operations support

**Endpoints**:

```
GET    /api/careers                     - List careers (with formats)
POST   /api/careers                     - Create career
GET    /api/careers/{id}                - Get career by ID
PUT    /api/careers/{id}                - Update career
DELETE /api/careers/{id}                - Delete career
POST   /api/careers/{id}/restore        - Restore career
GET    /api/careers/{id}/hierarchy      - Get hierarchy
GET    /api/careers/{id}/statistics     - Get statistics
GET    /api/careers/code/{code}         - Find by code
POST   /api/careers/bulk-delete         - Bulk delete
GET    /api/departments/{id}/careers    - Get careers by department
```

### 3. Request Classes

**Path**: `app/Http/Requests/Career/`

#### StoreCareerRequest

-   Name validation (required, 2-255 chars)
-   Code validation (optional, unique, uppercase, alphanumeric)
-   Department ID validation (required, exists)
-   Auto-normalization of input data

#### UpdateCareerRequest

-   Similar validation with `sometimes` rules
-   Unique code validation ignoring current record
-   Version control support

### 4. CareerResource Enhancement

**Path**: `app/Http/Resources/CareerResource.php`

**Added Methods**:

-   `forDropdown($collection)`: Dropdown format with department info
-   `withHierarchy()`: Complete hierarchy view with subsystems
-   `with(Request $request)`: Metadata inclusion

## API Usage Examples

### 1. Basic Listing

```bash
# Default collection
GET /api/careers

# Paginated
GET /api/careers?format=paginate&per_page=10

# Minimal view
GET /api/careers?format=minimal

# Dropdown format
GET /api/careers?format=dropdown

# Pluck format
GET /api/careers?format=pluck&pluck_key=id&pluck_label=name
```

### 2. Filtering

```bash
# Search by name or code
GET /api/careers?search=ingeniería

# Filter by department
GET /api/careers?department_id={uuid}

# Filter by code
GET /api/careers?code=ING_SISTEMAS

# Multiple filters
GET /api/careers?format=paginate&department_id={uuid}&search=sistemas
```

### 3. Relationships

```bash
# Include department
GET /api/careers/{id}?include=department

# Include subsystems
GET /api/careers/{id}?include=subsystems

# Include hierarchy (department + head_office + subsystems)
GET /api/careers/{id}?include=hierarchy
```

### 4. Department-specific Careers

```bash
# Get all careers for a department
GET /api/departments/{departmentId}/careers

# With formatting
GET /api/departments/{departmentId}/careers?format=dropdown
```

### 5. Create Career

```bash
POST /api/careers
Content-Type: application/json

{
    "name": "Ingeniería en Sistemas",
    "code": "ING_SISTEMAS",
    "department_id": "uuid-here"
}
```

### 6. Update Career

```bash
PUT /api/careers/{id}
Content-Type: application/json

{
    "name": "Ingeniería en Sistemas Actualizada",
    "code": "ING_SISTEMAS_UPD"
}
```

### 7. Bulk Operations

```bash
POST /api/careers/bulk-delete
Content-Type: application/json

{
    "ids": ["uuid1", "uuid2", "uuid3"]
}
```

## Database Relations

```
HeadOffice (1) -> Department (n) -> Career (n) <- (n) Subsystem
```

-   **Career** belongs to **Department**
-   **Career** has many **Subsystems** (many-to-many)
-   **Career** can access **HeadOffice** through Department

## Validation Rules

### Create Career

-   **name**: required, string, 2-255 chars
-   **code**: optional, unique, uppercase, alphanumeric with - and \_
-   **department_id**: required, valid UUID, must exist

### Update Career

-   All fields optional with `sometimes` rule
-   Code uniqueness ignores current record
-   Version control for optimistic locking

## Business Logic

### Delete Validation

-   Cannot delete career if it has active subsystems
-   Soft delete implementation
-   Cascade considerations for related entities

### Code Normalization

-   Automatically converts to uppercase
-   Validates format (A-Z, 0-9, -, \_)
-   Ensures uniqueness across all careers

### Department Validation

-   Validates department exists before creation/update
-   Maintains referential integrity

## Error Handling

### Common Errors

-   **422**: Validation errors (invalid data)
-   **404**: Career/Department not found
-   **400**: Business rule violations (e.g., delete with active subsystems)
-   **500**: Server errors

### Example Error Response

```json
{
    "success": false,
    "message": "Los datos proporcionados no son válidos.",
    "errors": {
        "name": ["El nombre es requerido"],
        "department_id": ["El departamento seleccionado no existe"]
    }
}
```

## Integration with ApiIndexBuilder

The Career controller fully leverages the new ApiIndexBuilder pattern:

```php
return catchSync(function () use ($request) {
    $filters = ApiIndexBuilder::extractFilters($request, ['department_id']);

    return ApiIndexBuilder::build(
        $this->careerService,
        CareerResource::class,
        $request,
        $filters
    );
}, 'Carreras obtenidas exitosamente');
```

## Future Enhancements

1. **Search Optimization**: Implement full-text search
2. **Caching**: Add Redis caching for frequently accessed data
3. **Export Features**: CSV/Excel export functionality
4. **Import Features**: Bulk import from files
5. **Advanced Filtering**: Date ranges, status filters
6. **Audit Trail**: Track all changes with detailed logging

## Testing

The implementation follows testable patterns:

-   Service layer for business logic testing
-   Resource transformation testing
-   API endpoint testing
-   Validation rule testing

## Performance Considerations

-   Eager loading of relationships to avoid N+1 queries
-   Pagination for large datasets
-   Indexed fields for common search operations
-   Optimistic locking for concurrent updates
