-- =====================================================================================
-- SCRIPT DE VERIFICACIÓN DE SINCRONIZACIÓN - MANAGEMENT DOCUMENT SERVICE
-- =====================================================================================
-- Descripción: Consultas para verificar que la base de datos está completamente sincronizada
-- Uso: Se ejecuta para validar el estado de la base de datos
-- =====================================================================================

-- Verificar versión de PostgreSQL
SELECT version();

-- =====================================================================================
-- VERIFICACIÓN DE EXTENSIONES
-- =====================================================================================
SELECT
    extname as extension_name,
    extversion as version
FROM pg_extension
WHERE extname IN ('uuid-ossp', 'plpgsql')
ORDER BY extname;

-- =====================================================================================
-- VERIFICACIÓN DE TABLAS PRINCIPALES
-- =====================================================================================
SELECT
    'TABLES' as check_type,
    COUNT(*) as total_count,
    string_agg(table_name, ', ' ORDER BY table_name) as items
FROM information_schema.tables
WHERE table_schema = 'public'
    AND table_type = 'BASE TABLE'
    AND table_name NOT LIKE 'pg_%';

-- Listado detallado de tablas esperadas
WITH expected_tables AS (
    SELECT unnest(ARRAY[
        'head_offices', 'departments', 'careers', 'subsystems', 'subsystem_entity_links',
        'subsystem_group_links', 'subsystem_groups',
        'process_categories', 'processes', 'document_types', 'academic_roles',
        'required_documents', 'storage_unit_types', 'storage_units',
        'metadata_schemas', 'metadata_fields', 'metadata_schema_events',
        'audit_logs', 'audit_metrics'
    ]) as table_name
),
existing_tables AS (
    SELECT table_name
    FROM information_schema.tables
    WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
)
SELECT
    et.table_name,
    CASE WHEN ext.table_name IS NOT NULL THEN ' EXISTS' ELSE '❌ MISSING' END as status
FROM expected_tables et
LEFT JOIN existing_tables ext ON et.table_name = ext.table_name
ORDER BY et.table_name;

-- =====================================================================================
-- VERIFICACIÓN DE FUNCIONES
-- =====================================================================================
SELECT
    'FUNCTIONS' as check_type,
    COUNT(*) as total_count,
    string_agg(routine_name, ', ' ORDER BY routine_name) as items
FROM information_schema.routines
WHERE routine_schema = 'public'
    AND routine_type = 'FUNCTION'
    AND routine_name IN (
        'update_updated_at_column',
        'refresh_process_hierarchy',
        'trigger_refresh_process_hierarchy'
    );

-- =====================================================================================
-- VERIFICACIÓN DE VISTAS Y VISTAS MATERIALIZADAS
-- =====================================================================================
SELECT
    'VIEWS' as check_type,
    table_type,
    COUNT(*) as count,
    string_agg(table_name, ', ' ORDER BY table_name) as items
FROM information_schema.tables
WHERE table_schema = 'public'
    AND table_type IN ('VIEW', 'MATERIALIZED VIEW')
GROUP BY table_type
ORDER BY table_type;

-- =====================================================================================
-- VERIFICACIÓN DE ÍNDICES IMPORTANTES
-- =====================================================================================
SELECT
    'INDEXES' as check_type,
    COUNT(*) as total_count
FROM pg_indexes
WHERE schemaname = 'public';

-- Verificar índices específicos importantes
SELECT
    indexname,
    tablename,
    CASE WHEN indexname IS NOT NULL THEN ' EXISTS' ELSE '❌ MISSING' END as status
FROM pg_indexes
WHERE schemaname = 'public'
    AND indexname IN (
        'audit_logs_pkey', 'metadata_schemas_pkey', 'head_offices_code_unique',
        'idx_audit_logs_table_record', 'idx_metadata_fields_schema_order'
    )
ORDER BY indexname;

-- =====================================================================================
-- VERIFICACIÓN DE CONSTRAINTS
-- =====================================================================================
SELECT
    'CONSTRAINTS' as check_type,
    constraint_type,
    COUNT(*) as count
FROM information_schema.table_constraints
WHERE table_schema = 'public'
    AND constraint_type IN ('PRIMARY KEY', 'FOREIGN KEY', 'UNIQUE', 'CHECK')
GROUP BY constraint_type
ORDER BY constraint_type;

-- =====================================================================================
-- VERIFICACIÓN DE TRIGGERS
-- =====================================================================================
SELECT
    'TRIGGERS' as check_type,
    COUNT(*) as total_count,
    COUNT(CASE WHEN trigger_name LIKE '%update%updated_at%' THEN 1 END) as update_triggers,
    COUNT(CASE WHEN trigger_name LIKE '%refresh_hierarchy%' THEN 1 END) as hierarchy_triggers
FROM information_schema.triggers
WHERE trigger_schema = 'public';

-- =====================================================================================
-- VERIFICACIÓN DE DATOS INICIALES
-- =====================================================================================


-- Academic Roles
SELECT
    'ACADEMIC_ROLES' as data_type,
    COUNT(*) as count,
    string_agg(code, ', ' ORDER BY code) as roles
FROM academic_roles
WHERE created_by = 'system';

-- Metadata Schemas
SELECT
    'METADATA_SCHEMAS' as data_type,
    COUNT(*) as count,
    string_agg(name, ', ' ORDER BY name) as schemas
FROM metadata_schemas
WHERE created_by = 'system';

-- =====================================================================================
-- VERIFICACIÓN DE INTEGRIDAD REFERENCIAL
-- =====================================================================================

-- Verificar que no hay registros huérfanos en careers
SELECT
    'REFERENTIAL_INTEGRITY' as check_type,
    'careers_departments' as relation,
    COUNT(*) as orphaned_records
FROM careers c
LEFT JOIN departments d ON c.department_id = d.id
WHERE d.id IS NULL;

-- Verificar que no hay registros huérfanos en departments
SELECT
    'REFERENTIAL_INTEGRITY' as check_type,
    'departments_head_offices' as relation,
    COUNT(*) as orphaned_records
FROM departments d
LEFT JOIN head_offices h ON d.head_office_id = h.id
WHERE h.id IS NULL;

-- =====================================================================================
-- RESUMEN FINAL
-- =====================================================================================
SELECT
    'SUMMARY' as check_type,
    'DATABASE_READY' as status,
    CASE
        WHEN (
            SELECT COUNT(*) FROM information_schema.tables
            WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
        ) >= 19
        AND (
            SELECT COUNT(*) FROM academic_roles WHERE created_by = 'system'
        ) >= 7
        THEN ' FULLY_SYNCHRONIZED'
        ELSE '❌ NEEDS_SYNC'
    END as sync_status,
    NOW() as check_timestamp;

-- =====================================================================================
-- FIN DE VERIFICACIONES
-- =====================================================================================
