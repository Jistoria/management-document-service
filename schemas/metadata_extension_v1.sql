-- =====================================================================================
-- METADATA EXTENSION V1 - SINCRONIZADO CON BASE DE DATOS ACTUAL
-- =====================================================================================
-- Fecha: 2025-07-12
-- Estado: SINCRONIZADO - Las tablas ya existen en la base de datos
-- Descripción: Gestión documental · módulo de metadatos ISO 16175‑1
-- =====================================================================================

-- =====================================================================================
-- NOTA: TABLAS YA IMPLEMENTADAS EN LA BASE DE DATOS
-- =====================================================================================
-- 
-- Las siguientes tablas YA EXISTEN en la base de datos con esquemas más completos:
-- 
-- ✅ metadata_schemas - CON COLUMNAS ADICIONALES:
--    - external_system_id, api_endpoint, cache_ttl
--    - created_by, updated_by, deleted_at
--    - triggers para updated_at
--
-- ✅ metadata_fields - CON COLUMNAS ADICIONALES:
--    - lookup_keywords (JSONB), ocr_hint
--    - ignore_in_similarity
--    - chk_valid_data_type constraint con tipos específicos
--    - created_at, updated_at
--
-- ✅ metadata_schema_events - CON COLUMNAS ADICIONALES:
--    - correlation_id, external_user_id, service_version
--
-- ✅ required_documents - YA MODIFICADO:
--    - metadata_schema_id (no schema_id)
--    - chk_required_documents_has_reference constraint
--    - índices para performance
--
-- =====================================================================================
-- CONSULTAS PARA VERIFICAR EL ESTADO ACTUAL
-- =====================================================================================

-- Verificar estructura de metadata_schemas
SELECT 
    column_name, 
    data_type, 
    is_nullable, 
    column_default
FROM information_schema.columns 
WHERE table_name = 'metadata_schemas' 
ORDER BY ordinal_position;

-- Verificar estructura de metadata_fields
SELECT 
    column_name, 
    data_type, 
    is_nullable, 
    column_default
FROM information_schema.columns 
WHERE table_name = 'metadata_fields' 
ORDER BY ordinal_position;

-- Verificar constraints en metadata_fields
SELECT 
    constraint_name, 
    constraint_type
FROM information_schema.table_constraints 
WHERE table_name = 'metadata_fields';

-- Verificar estructura de required_documents
SELECT 
    column_name, 
    data_type, 
    is_nullable
FROM information_schema.columns 
WHERE table_name = 'required_documents' 
    AND column_name IN ('metadata_schema_id', 'schema_id', 'security_level')
ORDER BY ordinal_position;

-- =====================================================================================
-- VALIDACIONES DE INTEGRIDAD
-- =====================================================================================

-- Verificar que existen esquemas de metadatos
SELECT 
    id,
    name,
    description,
    is_canonical,
    version,
    created_at
FROM metadata_schemas 
ORDER BY created_at;

-- Verificar campos de metadatos existentes
SELECT 
    mf.name as field_name,
    mf.data_type,
    mf.is_required,
    mf.is_reference,
    mf.reference_entity,
    ms.name as schema_name
FROM metadata_fields mf
JOIN metadata_schemas ms ON mf.schema_id = ms.id
ORDER BY ms.name, mf.field_order;

-- Verificar eventos del sistema de metadatos
SELECT 
    event_type,
    COUNT(*) as event_count,
    MAX(event_time) as last_event
FROM metadata_schema_events 
GROUP BY event_type
ORDER BY last_event DESC;

-- =====================================================================================
-- DATOS DE EJEMPLO PARA TESTING (SI SE NECESITAN)
-- =====================================================================================

-- Esquema básico de documento académico (si no existe)
INSERT INTO metadata_schemas (
    id, 
    name, 
    description, 
    is_canonical, 
    version, 
    created_by
) VALUES (
    gen_random_uuid(),
    'documento_academico',
    'Esquema para documentos académicos con metadatos ISO 16175-1',
    true,
    1,
    'system'
) ON CONFLICT DO NOTHING;

-- Campos básicos para documento académico
INSERT INTO metadata_fields (
    id,
    schema_id,
    name,
    data_type,
    is_required,
    field_order,
    is_reference
) SELECT 
    gen_random_uuid(),
    ms.id,
    field_data.name,
    field_data.data_type,
    field_data.is_required,
    field_data.field_order,
    field_data.is_reference
FROM metadata_schemas ms,
(VALUES 
    ('titulo', 'string', true, 1, false),
    ('fecha_creacion', 'date', true, 2, false),
    ('autor', 'string', true, 3, false),
    ('tipo_documento', 'uuid', true, 4, true),
    ('estado', 'string', false, 5, false)
) AS field_data(name, data_type, is_required, field_order, is_reference)
WHERE ms.name = 'documento_academico'
ON CONFLICT DO NOTHING;

-- =====================================================================================
-- FIN DEL ARCHIVO SINCRONIZADO
-- =====================================================================================
