-- =====================================================================================
-- MODIFICACIONES V3.1 - SISTEMA DE GESTIÓN DOCUMENTAL
-- =====================================================================================
-- Fecha: 2025-07-07
-- Descripción: Modificaciones para soportar documentos sin proceso específico,
--              relacionar metadata_schemas con required_documents y auditoría completa
-- =====================================================================================

-- 1. MODIFICAR TABLA required_documents
-- =====================================================================================

-- Hacer process_id nullable para permitir documentos que solo pertenezcan a un tipo
ALTER TABLE required_documents
ALTER COLUMN process_id DROP NOT NULL;

-- Agregar metadata_schema_id para relacionar con esquemas de metadatos
ALTER TABLE required_documents
ADD COLUMN metadata_schema_id UUID;

-- Agregar constraint para metadata_schema_id
ALTER TABLE required_documents
ADD CONSTRAINT required_documents_metadata_schema_id_foreign
    FOREIGN KEY (metadata_schema_id) REFERENCES metadata_schemas(id) ON DELETE SET NULL;

-- Agregar constraint para validar que tenga al menos process_id o metadata_schema_id
ALTER TABLE required_documents
ADD CONSTRAINT chk_required_documents_has_reference
    CHECK (process_id IS NOT NULL OR metadata_schema_id IS NOT NULL);

-- 2. CREAR TABLA DE AUDITORÍA COMPLETA
-- =====================================================================================

CREATE TABLE audit_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    -- Información básica del evento
    table_name VARCHAR(255) NOT NULL,
    record_id UUID NOT NULL,
    action VARCHAR(50) NOT NULL, -- 'INSERT', 'UPDATE', 'DELETE', 'SOFT_DELETE', 'RESTORE'

    -- Información del usuario y contexto
    user_id VARCHAR(255),
    external_user_id VARCHAR(255),
    user_email VARCHAR(255),
    user_name VARCHAR(255),
    ip_address INET,
    user_agent TEXT,

    -- Información de la aplicación
    service_name VARCHAR(100) DEFAULT 'management-document-service',
    service_version VARCHAR(50),
    endpoint VARCHAR(500),
    correlation_id UUID,
    session_id VARCHAR(255),

    -- Datos del cambio - JSON para flexibilidad
    old_values JSONB,
    new_values JSONB,
    changed_fields TEXT[], -- Array de campos que cambiaron

    -- Versionado y control
    record_version_before INTEGER,
    record_version_after INTEGER,

    -- Metadatos adicionales del cambio
    change_reason VARCHAR(500),
    change_metadata JSONB, -- Metadatos específicos del cambio
    business_context JSONB, -- Contexto de negocio

    -- Información temporal
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    -- Constraints
    CONSTRAINT chk_valid_action
        CHECK (action IN ('INSERT', 'UPDATE', 'DELETE', 'SOFT_DELETE', 'RESTORE', 'BULK_INSERT', 'BULK_UPDATE', 'BULK_DELETE')),
    CONSTRAINT chk_record_version_logic
        CHECK (
            (action = 'INSERT' AND record_version_before IS NULL) OR
            (action = 'UPDATE' AND record_version_before IS NOT NULL AND record_version_after >= record_version_before) OR
            (action IN ('DELETE', 'SOFT_DELETE') AND record_version_before IS NOT NULL) OR
            (action = 'RESTORE' AND record_version_before IS NOT NULL)
        )
);

-- 3. CREAR TABLA DE MÉTRICAS DE AUDITORÍA PARA REPORTES
-- =====================================================================================

CREATE TABLE audit_metrics (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    -- Información de la métrica
    metric_name VARCHAR(255) NOT NULL,
    table_name VARCHAR(255),
    action VARCHAR(50),
    user_id VARCHAR(255),

    -- Valores de la métrica
    count_value INTEGER DEFAULT 0,
    sum_value DECIMAL(15,2) DEFAULT 0,
    avg_value DECIMAL(15,2) DEFAULT 0,
    min_value DECIMAL(15,2),
    max_value DECIMAL(15,2),

    -- Información temporal - periodos agregados
    period_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    period_end TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    granularity VARCHAR(20) NOT NULL, -- 'hour', 'day', 'week', 'month'
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    -- Metadatos adicionales de la métrica
    metadata JSONB,

    -- Constraints
    CONSTRAINT chk_period_logic CHECK (period_end > period_start),
    CONSTRAINT chk_positive_counts CHECK (count_value >= 0),
    CONSTRAINT chk_valid_granularity CHECK (granularity IN ('hour', 'day', 'week', 'month'))
);

-- =====================================================================================
-- ÍNDICES PARA PERFORMANCE DE AUDITORÍA
-- =====================================================================================

-- Índices principales para audit_logs
CREATE INDEX idx_audit_logs_table_record
    ON audit_logs(table_name, record_id);

CREATE INDEX idx_audit_logs_user_time
    ON audit_logs(external_user_id, created_at DESC);

CREATE INDEX idx_audit_logs_action_time
    ON audit_logs(action, created_at DESC);

CREATE INDEX idx_audit_logs_correlation
    ON audit_logs(correlation_id) WHERE correlation_id IS NOT NULL;

CREATE INDEX idx_audit_logs_service_version
    ON audit_logs(service_name, service_version);

CREATE INDEX idx_audit_logs_session
    ON audit_logs(session_id) WHERE session_id IS NOT NULL;

-- Índice para búsquedas por campos cambiados usando GIN
CREATE INDEX idx_audit_logs_changed_fields
    ON audit_logs USING GIN(changed_fields);

-- Índices para JSONB metadata
CREATE INDEX idx_audit_logs_change_metadata
    ON audit_logs USING GIN(change_metadata);

CREATE INDEX idx_audit_logs_business_context
    ON audit_logs USING GIN(business_context);

-- Índices para required_documents modificado
CREATE INDEX idx_required_documents_metadata_schema
    ON required_documents(metadata_schema_id) WHERE metadata_schema_id IS NOT NULL;

CREATE INDEX idx_required_documents_process_nullable
    ON required_documents(process_id) WHERE process_id IS NOT NULL;

-- Índice compuesto para consultas mixtas
CREATE INDEX idx_required_documents_type_process_schema
    ON required_documents(document_type_id, process_id, metadata_schema_id)
    WHERE deleted_at IS NULL;

-- Índices para audit_metrics
CREATE INDEX idx_audit_metrics_table_action_period
    ON audit_metrics(table_name, action, period_start, period_end);

CREATE INDEX idx_audit_metrics_metric_period
    ON audit_metrics(metric_name, granularity, period_start DESC);

CREATE INDEX idx_audit_metrics_user_period
    ON audit_metrics(user_id, period_start DESC) WHERE user_id IS NOT NULL;

-- =====================================================================================
-- NOTA: FUNCIONES DE AUDITORÍA ELIMINADAS
-- =====================================================================================
--
-- Las funciones de auditoría automática mediante triggers han sido eliminadas.
-- La auditoría se maneja ahora completamente desde Laravel usando los traits:
-- - App\Traits\Auditable: Para auditoría automática en modelos
-- - App\Traits\HasAuditFields: Para campos de auditoría básicos
--
-- Las tablas audit_logs y audit_metrics se mantienen para uso desde Laravel.

-- =====================================================================================
-- NOTA: TRIGGERS DE AUDITORÍA ELIMINADOS
-- =====================================================================================
--
-- Los triggers automáticos de auditoría han sido eliminados.
-- La auditoría se maneja ahora desde Laravel usando el trait Auditable.
--
-- Para usar auditoría en un modelo, simplemente agregar:
-- use App\Traits\Auditable;
--
-- Los registros se crearán automáticamente en la tabla audit_logs.

-- =====================================================================================
-- VISTAS PARA CONSULTAS DE AUDITORÍA
-- =====================================================================================

-- Vista para auditoría resumida por usuario
CREATE VIEW v_audit_summary_by_user AS
SELECT
    external_user_id,
    user_name,
    table_name,
    action,
    COUNT(*) as operation_count,
    MIN(created_at) as first_operation,
    MAX(created_at) as last_operation,
    array_agg(DISTINCT record_id) as affected_records
FROM audit_logs
WHERE external_user_id IS NOT NULL
GROUP BY external_user_id, user_name, table_name, action;

-- Vista para cambios recientes
CREATE VIEW v_recent_changes AS
SELECT
    al.id,
    al.table_name,
    al.record_id,
    al.action,
    al.external_user_id,
    al.created_at,
    al.changed_fields,
    al.change_metadata ->> 'summary' as change_summary,
    array_length(al.changed_fields, 1) as fields_changed_count
FROM audit_logs al
WHERE al.created_at >= NOW() - INTERVAL '24 hours'
ORDER BY al.created_at DESC;

-- =====================================================================================
-- COMENTARIOS Y DOCUMENTACIÓN
-- =====================================================================================

-- Comentarios en required_documents modificado
COMMENT ON COLUMN required_documents.process_id IS 'ID del proceso (nullable) - permite documentos independientes de procesos específicos';
COMMENT ON COLUMN required_documents.metadata_schema_id IS 'ID del esquema de metadatos - permite reutilizar esquemas normalizados entre documentos';
COMMENT ON CONSTRAINT chk_required_documents_has_reference ON required_documents IS 'Garantiza que el documento tenga al menos una referencia: process_id o metadata_schema_id';

-- Comentarios en audit_logs
COMMENT ON TABLE audit_logs IS 'Registro completo de auditoría con metadatos detallados para todas las operaciones del sistema';
COMMENT ON COLUMN audit_logs.table_name IS 'Nombre de la tabla afectada';
COMMENT ON COLUMN audit_logs.record_id IS 'ID del registro afectado';
COMMENT ON COLUMN audit_logs.action IS 'Tipo de acción: INSERT, UPDATE, DELETE, SOFT_DELETE, RESTORE, BULK_*';
COMMENT ON COLUMN audit_logs.changed_fields IS 'Array de campos que fueron modificados en la operación';
COMMENT ON COLUMN audit_logs.old_values IS 'Valores anteriores del registro completo (JSON)';
COMMENT ON COLUMN audit_logs.new_values IS 'Valores nuevos del registro completo (JSON)';
COMMENT ON COLUMN audit_logs.change_metadata IS 'Metadatos detallados del cambio incluyendo diferencias campo por campo';
COMMENT ON COLUMN audit_logs.business_context IS 'Contexto de negocio y categorización del tipo de operación';
COMMENT ON COLUMN audit_logs.correlation_id IS 'ID para trazabilidad distribuida entre microservicios';
COMMENT ON COLUMN audit_logs.record_version_before IS 'Versión del registro antes del cambio';
COMMENT ON COLUMN audit_logs.record_version_after IS 'Versión del registro después del cambio';

-- Comentarios en audit_metrics
COMMENT ON TABLE audit_metrics IS 'Métricas agregadas de auditoría para reportes, dashboards y análisis de patrones';
COMMENT ON COLUMN audit_metrics.metric_name IS 'Nombre de la métrica (ej: daily_inserts, user_activity, table_modifications)';
COMMENT ON COLUMN audit_metrics.granularity IS 'Granularidad temporal: hour, day, week, month';
COMMENT ON COLUMN audit_metrics.period_start IS 'Inicio del período de agregación de la métrica';
COMMENT ON COLUMN audit_metrics.period_end IS 'Fin del período de agregación de la métrica';

-- Comentarios en vistas
COMMENT ON VIEW v_audit_summary_by_user IS 'Resumen de actividad de auditoría agrupada por usuario para reportes';
COMMENT ON VIEW v_recent_changes IS 'Vista de cambios recientes (últimas 24h) para monitoreo en tiempo real';

-- =====================================================================================
-- FIN DE MODIFICACIONES V3.1
-- =====================================================================================
