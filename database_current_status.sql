-- =====================================================================================
-- DATABASE STATUS V2025.07.12 - ESTADO ACTUAL DE LA BASE DE DATOS
-- =====================================================================================
-- Fecha: 2025-07-12
-- Descripción: Reflejo exacto del estado actual de la base de datos
-- Este archivo documenta la estructura real implementada
-- =====================================================================================

-- =====================================================================================
-- RESUMEN DE ESTADO
-- =====================================================================================
-- ✅ IMPLEMENTADO: Sistema completo de metadatos
-- ✅ IMPLEMENTADO: Sistema de auditoría completa
-- ✅ IMPLEMENTADO: Todas las tablas principales del dominio
-- ✅ IMPLEMENTADO: Vistas materializadas y funciones de mantenimiento
-- ✅ IMPLEMENTADO: Índices optimizados para performance
-- ✅ IMPLEMENTADO: Constraints e integridad referencial

-- =====================================================================================
-- TABLAS PRINCIPALES DEL DOMINIO
-- =====================================================================================

-- ADMINISTRACIÓN
-- head_offices: Sedes principales con código único
-- departments: Departamentos que pertenecen a sedes
-- careers: Carreras académicas con relación a departamentos
-- subsystems: Subsistemas del sistema de gestión
-- careers_subsystems: Relación many-to-many carreras-subsistemas

-- PROCESOS
-- process_categories: Categorías de procesos por subsistema
-- processes: Procesos específicos con soporte jerárquico (parent_id)

-- DOCUMENTOS
-- document_types: Tipos de documentos con código único
-- academic_roles: Roles académicos (estudiante, docente, coordinador, etc.)
-- required_documents: Documentos requeridos por proceso con metadata_schema_id

-- ALMACENAMIENTO
-- storage_unit_types: Tipos de unidades de almacenamiento con niveles
-- storage_units: Unidades de almacenamiento jerárquicas

-- =====================================================================================
-- SISTEMA DE METADATOS (ISO 16175-1)
-- =====================================================================================

-- metadata_schemas: Esquemas de metadatos con herencia y versionado
--   ✅ parent_schema_id para herencia
--   ✅ external_system_id para integración
--   ✅ api_endpoint para datos dinámicos
--   ✅ cache_ttl para optimización
--   ✅ soft deletes con deleted_at

-- metadata_fields: Campos de metadatos con validación avanzada
--   ✅ lookup_keywords (JSONB) para búsquedas
--   ✅ ocr_hint para reconocimiento óptico
--   ✅ is_reference para campos relacionales
--   ✅ reference_entity y reference_column
--   ✅ chk_valid_data_type constraint con tipos específicos

-- metadata_schema_events: Eventos y auditoría del sistema de metadatos
--   ✅ correlation_id para tracing distribuido
--   ✅ external_user_id para usuarios de microservicios
--   ✅ service_version para versionado de servicio

-- =====================================================================================
-- SISTEMA DE AUDITORÍA COMPLETA
-- =====================================================================================

-- audit_logs: Registro detallado de todas las operaciones
--   ✅ Información completa del usuario (id, email, name, IP, user_agent)
--   ✅ Contexto de servicio (service_name, version, endpoint)
--   ✅ Trazabilidad distribuida (correlation_id, session_id)
--   ✅ Datos del cambio (old_values, new_values JSONB)
--   ✅ Metadatos de negocio (change_metadata, business_context)
--   ✅ Versionado de registros (record_version_before/after)

-- audit_metrics: Métricas agregadas para dashboards
--   ✅ Múltiples valores estadísticos (count, sum, avg, min, max)
--   ✅ Períodos configurables (hour, day, week, month)
--   ✅ Metadatos adicionales en JSONB

-- =====================================================================================
-- VISTAS Y FUNCIONES
-- =====================================================================================

-- VISTAS MATERIALIZADAS:
-- mv_process_hierarchy: Jerarquía completa de procesos con path y levels

-- VISTAS REGULARES:
-- v_audit_summary_by_user: Resumen de actividad por usuario
-- v_recent_changes: Cambios recientes (últimas 24h)

-- FUNCIONES:
-- update_updated_at_column(): Actualización automática de timestamps
-- refresh_process_hierarchy(): Mantenimiento de vista materializada
-- trigger_refresh_process_hierarchy(): Trigger para refresh automático

-- =====================================================================================
-- CONFIGURACIÓN DE MICROSERVICIO
-- =====================================================================================

-- external_apis: Configuración de APIs externas
--   ✅ Configuración de autenticación (bearer, basic, api_key, oauth2)
--   ✅ Timeouts y reintentos configurables
--   ✅ Estado activo/inactivo

-- migrations: Control de migraciones Laravel
--   ✅ Secuencia automática con SERIAL

-- =====================================================================================
-- CAMPOS DE AUDITORÍA ESTÁNDAR EN TODAS LAS TABLAS
-- =====================================================================================

-- Campos comunes implementados:
-- - id: UUID PRIMARY KEY
-- - created_at, updated_at: timestamps automáticos
-- - deleted_at: soft deletes
-- - created_by, updated_by: usuarios externos
-- - version: control de versiones (donde aplique)

-- =====================================================================================
-- ÍNDICES IMPLEMENTADOS PARA PERFORMANCE
-- =====================================================================================

-- JERARQUÍAS Y RELACIONES:
-- idx_careers_department_active: careers(department_id) WHERE deleted_at IS NULL
-- idx_processes_category_order: processes(process_category_id, order) WHERE deleted_at IS NULL
-- idx_required_documents_process_order: required_documents(process_id, order) WHERE deleted_at IS NULL

-- METADATOS:
-- idx_metadata_fields_schema_order: metadata_fields(schema_id, field_order)
-- idx_metadata_schema_events_correlation: metadata_schema_events(correlation_id)
-- idx_metadata_schema_events_external_user: metadata_schema_events(external_user_id)

-- AUDITORÍA:
-- idx_audit_logs_table_record: audit_logs(table_name, record_id)
-- idx_audit_logs_user_time: audit_logs(external_user_id, created_at DESC)
-- idx_audit_logs_action_time: audit_logs(action, created_at DESC)
-- idx_audit_logs_changed_fields: GIN index para arrays
-- idx_audit_logs_change_metadata: GIN index para JSONB
-- idx_audit_logs_business_context: GIN index para JSONB

-- CÓDIGOS Y BÚSQUEDAS:
-- idx_head_offices_code_active: head_offices(code) WHERE deleted_at IS NULL
-- idx_subsystems_code_active: subsystems(code) WHERE deleted_at IS NULL

-- APIs EXTERNAS:
-- idx_external_apis_service_active: external_apis(service_name) WHERE is_active = true

-- VISTAS MATERIALIZADAS:
-- idx_mv_process_hierarchy_category: mv_process_hierarchy(process_category_id, level)
-- idx_mv_process_hierarchy_parent: mv_process_hierarchy(parent_id)

-- =====================================================================================
-- CONSTRAINTS DE INTEGRIDAD IMPLEMENTADOS
-- =====================================================================================

-- VERSIONES Y VALORES POSITIVOS:
-- chk_version_positive: version > 0 (multiple tables)
-- chk_level_positive: level > 0 (storage_unit_types)
-- chk_order_non_negative: order >= 0 (processes, required_documents)
-- chk_field_order_positive: field_order > 0 (metadata_fields)

-- VALIDACIONES DE TIPOS:
-- chk_valid_data_type: tipos específicos en metadata_fields
-- chk_valid_action: acciones válidas en audit_logs
-- chk_valid_granularity: granularidades válidas en audit_metrics

-- LÓGICA DE NEGOCIO:
-- chk_required_documents_has_reference: process_id OR metadata_schema_id NOT NULL
-- chk_period_logic: period_end > period_start (audit_metrics)
-- chk_positive_counts: count_value >= 0 (audit_metrics)
-- chk_record_version_logic: lógica compleja de versionado (audit_logs)

-- =====================================================================================
-- DATOS INICIALES IMPLEMENTADOS
-- =====================================================================================

-- external_apis: Configuración inicial de microservicios
-- metadata_schemas: Esquema 'system' para eventos internos
-- document_types: Tipos básicos (académico, administrativo, certificado, formulario)
-- academic_roles: Roles básicos (estudiante, docente, coordinador, decano, rector)

-- =====================================================================================
-- TRIGGERS IMPLEMENTADOS
-- =====================================================================================

-- TIMESTAMPS AUTOMÁTICOS:
-- trigger_update_*_updated_at: Para todas las tablas principales

-- MANTENIMIENTO DE VISTAS:
-- trigger_processes_refresh_hierarchy: Refresh automático en changes de processes
-- trigger_process_categories_refresh_hierarchy: Refresh automático en changes de categories

-- =====================================================================================
-- ESTADO DE COMPATIBILIDAD
-- =====================================================================================

-- ✅ PostgreSQL 16.8 compatible
-- ✅ Laravel 11 compatible
-- ✅ Extensiones requeridas: uuid-ossp, plpgsql
-- ✅ Microservicios architecture ready
-- ✅ Auditoría completa implementada
-- ✅ Performance optimized con índices

-- =====================================================================================
-- FIN DEL REPORTE DE ESTADO
-- =====================================================================================
