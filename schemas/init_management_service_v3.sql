-- =====================================================================================
-- MICROSERVICIO DE GESTIÓN DOCUMENTAL - SCRIPT COMPLETO DE BASE DE DATOS
-- =====================================================================================
-- Versión: 2.0
-- Fecha: 2025-07-04
-- Descripción: Base de datos optimizada para microservicio de gestión documental
-- =====================================================================================

-- Habilitar extensiones necesarias
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS plpgsql;

-- =====================================================================================
-- TABLAS PRINCIPALES DEL DOMINIO
-- =====================================================================================

-- Tabla: Sedes principales de la organización
CREATE TABLE head_offices (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Campos de auditoría para microservicios
    created_by VARCHAR(255),
    updated_by VARCHAR(255),
    version INTEGER DEFAULT 1 NOT NULL
);

-- Tabla: Departamentos que pertenecen a las sedes
CREATE TABLE departments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    head_office_id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255),
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Campos de auditoría para microservicios
    created_by VARCHAR(255),
    updated_by VARCHAR(255),
    version INTEGER DEFAULT 1 NOT NULL,
    -- Constraints
    CONSTRAINT departments_head_office_id_foreign
        FOREIGN KEY (head_office_id) REFERENCES head_offices(id) ON DELETE CASCADE,
    CONSTRAINT chk_version_positive CHECK (version > 0)
);

-- Tabla: Carreras que pertenecen a los departamentos
CREATE TABLE careers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    department_id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255),
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Campos de auditoría para microservicios
    created_by VARCHAR(255),
    updated_by VARCHAR(255),
    version INTEGER DEFAULT 1 NOT NULL,
    -- Constraints
    CONSTRAINT careers_department_id_foreign
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    CONSTRAINT chk_version_positive CHECK (version > 0)
);

-- Tabla: Subsistemas del sistema de gestión
CREATE TABLE subsystems (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Campos de auditoría para microservicios
    created_by VARCHAR(255),
    updated_by VARCHAR(255),
    version INTEGER DEFAULT 1 NOT NULL,
    -- Constraints
    CONSTRAINT chk_version_positive CHECK (version > 0)
);

-- Tabla: Relación many-to-many entre carreras y subsistemas
CREATE TABLE careers_subsystems (
    career_id UUID NOT NULL,
    subsystem_id UUID NOT NULL,
    PRIMARY KEY (career_id, subsystem_id),
    -- Constraints
    CONSTRAINT careers_subsystems_career_id_foreign
        FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
    CONSTRAINT careers_subsystems_subsystem_id_foreign
        FOREIGN KEY (subsystem_id) REFERENCES subsystems(id) ON DELETE CASCADE
);

-- =====================================================================================
-- TABLAS DE GESTIÓN DE PROCESOS
-- =====================================================================================

-- Tabla: Categorías de procesos
CREATE TABLE process_categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    subsystem_id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255),
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Constraints
    CONSTRAINT process_categories_subsystem_id_foreign
        FOREIGN KEY (subsystem_id) REFERENCES subsystems(id) ON DELETE CASCADE
);

-- Tabla: Procesos específicos con soporte para jerarquías
CREATE TABLE processes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    process_category_id UUID NOT NULL,
    parent_id UUID,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255),
    "order" INTEGER DEFAULT 0 NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Constraints
    CONSTRAINT processes_process_category_id_foreign
        FOREIGN KEY (process_category_id) REFERENCES process_categories(id) ON DELETE CASCADE,
    CONSTRAINT processes_parent_id_foreign
        FOREIGN KEY (parent_id) REFERENCES processes(id) ON DELETE CASCADE,
    CONSTRAINT chk_order_non_negative CHECK ("order" >= 0)
);

-- =====================================================================================
-- TABLAS DE GESTIÓN DOCUMENTAL
-- =====================================================================================

-- Tabla: Tipos de documentos
CREATE TABLE document_types (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Campos de auditoría para microservicios
    created_by VARCHAR(255),
    updated_by VARCHAR(255),
    version INTEGER DEFAULT 1 NOT NULL,
    -- Constraints
    CONSTRAINT chk_version_positive CHECK (version > 0)
);

-- Tabla: Roles académicos
CREATE TABLE academic_roles (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Campos de auditoría para microservicios
    created_by VARCHAR(255),
    updated_by VARCHAR(255),
    version INTEGER DEFAULT 1 NOT NULL,
    -- Constraints
    CONSTRAINT chk_version_positive CHECK (version > 0)
);

-- Tabla: Documentos requeridos por proceso
CREATE TABLE required_documents (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    process_id UUID NOT NULL,
    document_type_id UUID NOT NULL,
    academic_role_id UUID,
    "order" INTEGER DEFAULT 0 NOT NULL,
    mandatory BOOLEAN DEFAULT true NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Campos de referencia externa para microservicios
    external_user_id VARCHAR(255),
    external_organization_id VARCHAR(255),
    -- Constraints
    CONSTRAINT required_documents_process_id_foreign
        FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE,
    CONSTRAINT required_documents_document_type_id_foreign
        FOREIGN KEY (document_type_id) REFERENCES document_types(id) ON DELETE CASCADE,
    CONSTRAINT required_documents_academic_role_id_foreign
        FOREIGN KEY (academic_role_id) REFERENCES academic_roles(id) ON DELETE SET NULL,
    CONSTRAINT chk_order_non_negative CHECK ("order" >= 0)
);

-- =====================================================================================
-- TABLAS DE ALMACENAMIENTO
-- =====================================================================================

-- Tabla: Tipos de unidades de almacenamiento
CREATE TABLE storage_unit_types (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL UNIQUE,
    level INTEGER NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Constraints
    CONSTRAINT chk_level_positive CHECK (level > 0)
);

-- Tabla: Unidades de almacenamiento con jerarquía
CREATE TABLE storage_units (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    storage_unit_type_id UUID NOT NULL,
    parent_id UUID,
    label VARCHAR(255) NOT NULL,
    code VARCHAR(255),
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Constraints
    CONSTRAINT storage_units_storage_unit_type_id_foreign
        FOREIGN KEY (storage_unit_type_id) REFERENCES storage_unit_types(id) ON DELETE CASCADE,
    CONSTRAINT storage_units_parent_id_foreign
        FOREIGN KEY (parent_id) REFERENCES storage_units(id) ON DELETE CASCADE
);

-- =====================================================================================
-- SISTEMA DE METADATOS AVANZADO
-- =====================================================================================

-- Tabla: Esquemas de metadatos con versionado y herencia
CREATE TABLE metadata_schemas (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    parent_schema_id UUID,
    is_canonical BOOLEAN DEFAULT false NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    -- Campos para integración con microservicios
    external_system_id VARCHAR(255),
    api_endpoint VARCHAR(500),
    cache_ttl INTEGER DEFAULT 3600,
    created_by VARCHAR(255),
    updated_by VARCHAR(255),
    -- Constraints
    CONSTRAINT metadata_schemas_parent_schema_id_foreign
        FOREIGN KEY (parent_schema_id) REFERENCES metadata_schemas(id) ON DELETE SET NULL
);

-- Tabla: Campos de metadatos con validación y soporte OCR
CREATE TABLE metadata_fields (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    schema_id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    data_type VARCHAR(255) NOT NULL,
    is_required BOOLEAN DEFAULT false NOT NULL,
    default_value TEXT,
    validation_regex VARCHAR(255),
    field_order INTEGER,
    lookup_keywords JSONB,
    ocr_hint VARCHAR(255),
    ignore_in_similarity BOOLEAN DEFAULT false NOT NULL,
    is_reference BOOLEAN DEFAULT false NOT NULL,
    reference_entity VARCHAR(255),
    reference_column VARCHAR(255) DEFAULT 'id' NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    -- Constraints
    CONSTRAINT metadata_fields_schema_id_foreign
        FOREIGN KEY (schema_id) REFERENCES metadata_schemas(id) ON DELETE CASCADE,
    CONSTRAINT chk_valid_data_type
        CHECK (data_type IN ('string', 'integer', 'decimal', 'date', 'boolean', 'json', 'uuid', 'text', 'email', 'url')),
    CONSTRAINT chk_field_order_positive CHECK (field_order > 0)
);

-- Tabla: Eventos del sistema de metadatos para auditoría
CREATE TABLE metadata_schema_events (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    schema_id UUID NOT NULL,
    event_type VARCHAR(255) NOT NULL,
    actor_id UUID,
    event_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    details JSONB,
    -- Campos para trazabilidad en microservicios
    correlation_id UUID,
    external_user_id VARCHAR(255),
    service_version VARCHAR(50),
    -- Constraints
    CONSTRAINT metadata_schema_events_schema_id_foreign
        FOREIGN KEY (schema_id) REFERENCES metadata_schemas(id) ON DELETE CASCADE
);

-- =====================================================================================
-- CONFIGURACIÓN DE MICROSERVICIOS
-- =====================================================================================

-- Tabla: Configuración de APIs externas
CREATE TABLE external_apis (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    service_name VARCHAR(255) NOT NULL,
    base_url VARCHAR(500) NOT NULL,
    auth_method VARCHAR(50) NOT NULL,
    timeout_seconds INTEGER DEFAULT 30,
    retry_attempts INTEGER DEFAULT 3,
    is_active BOOLEAN DEFAULT true NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(255),
    updated_by VARCHAR(255),
    -- Constraints
    CONSTRAINT chk_auth_method
        CHECK (auth_method IN ('bearer', 'basic', 'api_key', 'oauth2')),
    CONSTRAINT chk_timeout_positive CHECK (timeout_seconds > 0),
    CONSTRAINT chk_retry_non_negative CHECK (retry_attempts >= 0)
);

-- Tabla: Migraciones de Laravel
CREATE TABLE migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

-- =====================================================================================
-- ÍNDICES PARA PERFORMANCE
-- =====================================================================================

-- Índices para consultas de jerarquías activas
CREATE INDEX idx_careers_department_active
    ON careers(department_id) WHERE deleted_at IS NULL;

CREATE INDEX idx_processes_category_order
    ON processes(process_category_id, "order") WHERE deleted_at IS NULL;

CREATE INDEX idx_required_documents_process_order
    ON required_documents(process_id, "order") WHERE deleted_at IS NULL;

-- Índices para sistema de metadatos
CREATE INDEX idx_metadata_fields_schema_order
    ON metadata_fields(schema_id, field_order);

CREATE INDEX idx_metadata_schema_events_correlation
    ON metadata_schema_events(correlation_id);

CREATE INDEX idx_metadata_schema_events_external_user
    ON metadata_schema_events(external_user_id);

-- Índices para auditoría
CREATE INDEX idx_document_types_created_by
    ON document_types(created_by) WHERE deleted_at IS NULL;

-- Índices para búsquedas por código
CREATE INDEX idx_head_offices_code_active
    ON head_offices(code) WHERE deleted_at IS NULL;

CREATE INDEX idx_subsystems_code_active
    ON subsystems(code) WHERE deleted_at IS NULL;

-- Índices para APIs externas
CREATE INDEX idx_external_apis_service_active
    ON external_apis(service_name) WHERE is_active = true;

-- =====================================================================================
-- FUNCIONES Y TRIGGERS
-- =====================================================================================

-- Función para actualizar timestamps automáticamente
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Triggers para actualización automática de timestamps
CREATE TRIGGER trigger_update_head_offices_updated_at
    BEFORE UPDATE ON head_offices
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_update_departments_updated_at
    BEFORE UPDATE ON departments
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_update_careers_updated_at
    BEFORE UPDATE ON careers
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_update_subsystems_updated_at
    BEFORE UPDATE ON subsystems
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_update_document_types_updated_at
    BEFORE UPDATE ON document_types
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_update_academic_roles_updated_at
    BEFORE UPDATE ON academic_roles
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_update_metadata_schemas_updated_at
    BEFORE UPDATE ON metadata_schemas
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- =====================================================================================
-- VISTAS MATERIALIZADAS PARA PERFORMANCE
-- =====================================================================================

-- Vista materializada para jerarquías de procesos
CREATE MATERIALIZED VIEW mv_process_hierarchy AS
WITH RECURSIVE process_tree AS (
    -- Nodos raíz (sin padre)
    SELECT
        id,
        parent_id,
        name,
        code,
        process_category_id,
        "order",
        0 as level,
        ARRAY[id] as path,
        name::TEXT as full_path
    FROM processes
    WHERE parent_id IS NULL
        AND deleted_at IS NULL

    UNION ALL

    -- Nodos hijos (recursivo)
    SELECT
        p.id,
        p.parent_id,
        p.name,
        p.code,
        p.process_category_id,
        p."order",
        pt.level + 1,
        pt.path || p.id,
        (pt.full_path || ' > ' || p.name)::TEXT
    FROM processes p
    JOIN process_tree pt ON p.parent_id = pt.id
    WHERE p.deleted_at IS NULL
)
SELECT
    pt.*,
    pc.name as category_name,
    pc.code as category_code
FROM process_tree pt
JOIN process_categories pc ON pt.process_category_id = pc.id
WHERE pc.deleted_at IS NULL;

-- Índices en la vista materializada
CREATE INDEX idx_mv_process_hierarchy_category
    ON mv_process_hierarchy(process_category_id, level);

CREATE INDEX idx_mv_process_hierarchy_parent
    ON mv_process_hierarchy(parent_id);

-- =====================================================================================
-- FUNCIONES DE MANTENIMIENTO
-- =====================================================================================

-- Función para refrescar vista materializada
CREATE OR REPLACE FUNCTION refresh_process_hierarchy()
RETURNS VOID AS $$
BEGIN
    REFRESH MATERIALIZED VIEW CONCURRENTLY mv_process_hierarchy;
    INSERT INTO metadata_schema_events (
        id,
        schema_id,
        event_type,
        event_time,
        details,
        service_version
    ) VALUES (
        gen_random_uuid(),
        (SELECT id FROM metadata_schemas WHERE name = 'system' LIMIT 1),
        'materialized_view_refresh',
        CURRENT_TIMESTAMP,
        '{"view": "mv_process_hierarchy", "status": "success"}'::jsonb,
        '1.0.0'
    );
EXCEPTION
    WHEN OTHERS THEN
        -- Si falla el refresh concurrente, usar refresh normal
        REFRESH MATERIALIZED VIEW mv_process_hierarchy;
END;
$$ LANGUAGE plpgsql;

-- Función trigger para refrescar vista automáticamente
CREATE OR REPLACE FUNCTION trigger_refresh_process_hierarchy()
RETURNS TRIGGER AS $$
BEGIN
    PERFORM refresh_process_hierarchy();
    RETURN COALESCE(NEW, OLD);
END;
$$ LANGUAGE plpgsql;

-- Triggers para mantener vista materializada actualizada
CREATE TRIGGER trigger_processes_refresh_hierarchy
    AFTER INSERT OR UPDATE OR DELETE ON processes
    FOR EACH STATEMENT EXECUTE FUNCTION trigger_refresh_process_hierarchy();

CREATE TRIGGER trigger_process_categories_refresh_hierarchy
    AFTER INSERT OR UPDATE OR DELETE ON process_categories
    FOR EACH STATEMENT EXECUTE FUNCTION trigger_refresh_process_hierarchy();

-- =====================================================================================
-- COMENTARIOS DESCRIPTIVOS
-- =====================================================================================

-- Comentarios en tablas principales
COMMENT ON TABLE head_offices IS 'Sedes principales de la organización';
COMMENT ON TABLE departments IS 'Departamentos que pertenecen a las sedes';
COMMENT ON TABLE careers IS 'Carreras académicas que pertenecen a los departamentos';
COMMENT ON TABLE subsystems IS 'Subsistemas del sistema de gestión documental';
COMMENT ON TABLE external_apis IS 'Configuración de APIs externas para integración entre microservicios';
COMMENT ON TABLE metadata_schemas IS 'Esquemas de metadatos con versionado y herencia';
COMMENT ON MATERIALIZED VIEW mv_process_hierarchy IS 'Vista materializada con jerarquía completa de procesos para consultas optimizadas';

-- Comentarios en campos críticos
COMMENT ON COLUMN required_documents.external_user_id IS 'ID del usuario desde microservicio de autenticación';
COMMENT ON COLUMN required_documents.external_organization_id IS 'ID de organización externa';
COMMENT ON COLUMN document_types.created_by IS 'ID usuario externo que creó el registro';
COMMENT ON COLUMN document_types.updated_by IS 'ID usuario externo que actualizó el registro';
COMMENT ON COLUMN document_types.version IS 'Versión del documento para control de cambios';
COMMENT ON COLUMN metadata_schemas.external_system_id IS 'ID del sistema externo para integración';
COMMENT ON COLUMN metadata_schemas.api_endpoint IS 'Endpoint para obtener datos dinámicos';
COMMENT ON COLUMN metadata_schemas.cache_ttl IS 'TTL en segundos para cache de metadatos';
COMMENT ON COLUMN metadata_schema_events.correlation_id IS 'ID para tracing distribuido entre microservicios';
COMMENT ON COLUMN metadata_schema_events.external_user_id IS 'Usuario desde microservicio externo';
COMMENT ON COLUMN metadata_schema_events.service_version IS 'Versión del servicio que hizo el cambio';
COMMENT ON COLUMN external_apis.auth_method IS 'Método de autenticación: bearer, basic, api_key, oauth2';

-- =====================================================================================
-- DATOS INICIALES DE CONFIGURACIÓN
-- =====================================================================================

-- Configuración inicial de APIs externas
INSERT INTO external_apis (service_name, base_url, auth_method, timeout_seconds, created_by) VALUES
('auth-service', 'https://auth.example.com/api/v1', 'bearer', 30, 'system'),
('user-service', 'https://users.example.com/api/v1', 'bearer', 30, 'system'),
('file-storage-service', 'https://storage.example.com/api/v1', 'api_key', 60, 'system'),
('notification-service', 'https://notifications.example.com/api/v1', 'bearer', 15, 'system');

-- Esquema de metadatos del sistema
INSERT INTO metadata_schemas (id, name, description, is_canonical, version, created_by) VALUES
(gen_random_uuid(), 'system', 'Esquema de metadatos del sistema para eventos internos', true, 1, 'system');

-- Tipos de documentos básicos
INSERT INTO document_types (id, name, code, description, created_by, version) VALUES
(gen_random_uuid(), 'Documento Académico', 'DOC_ACAD', 'Documentos relacionados con procesos académicos', 'system', 1),
(gen_random_uuid(), 'Documento Administrativo', 'DOC_ADMIN', 'Documentos relacionados con procesos administrativos', 'system', 1),
(gen_random_uuid(), 'Certificado', 'CERT', 'Certificaciones y constancias oficiales', 'system', 1),
(gen_random_uuid(), 'Formulario', 'FORM', 'Formularios y solicitudes', 'system', 1);

-- Roles académicos básicos
INSERT INTO academic_roles (id, name, code, created_by, version) VALUES
(gen_random_uuid(), 'Estudiante', 'EST', 'system', 1),
(gen_random_uuid(), 'Docente', 'DOC', 'system', 1),
(gen_random_uuid(), 'Coordinador', 'COORD', 'system', 1),
(gen_random_uuid(), 'Decano', 'DECANO', 'system', 1),
(gen_random_uuid(), 'Rector', 'RECTOR', 'system', 1);

-- =====================================================================================
-- FIN DEL SCRIPT
-- =====================================================================================
-- Este script crea una base de datos completa y optimizada para un microservicio
-- de gestión documental con las siguientes características:
--
--  Arquitectura de microservicio (sin dependencias de auth/cache/jobs)
--  Campos de referencia externa para integración
--  Sistema de auditoría y versionado completo
--  Índices optimizados para performance
--  Vistas materializadas para consultas complejas
--  Triggers automáticos para mantenimiento
--  Validaciones de negocio robustas
--  Sistema de metadatos flexible y extensible
--  Configuración inicial lista para uso
-- =====================================================================================
