-- =====================================================================================
-- SCRIPT COMPLETO DE RECREACIÓN DE BASE DE DATOS - MANAGEMENT DOCUMENT SERVICE
-- =====================================================================================
-- Versión: 4.0
-- Fecha: 2025-07-12
-- Descripción: Script completo para recrear la base de datos desde cero
-- Incluye: Extensiones, Secuencias, Tablas, Índices, Constraints, Funciones,
--          Triggers, Vistas, Vistas Materializadas y Datos Iniciales
-- =====================================================================================

-- =====================================================================================
-- CONFIGURACIÓN INICIAL
-- =====================================================================================

-- Habilitar extensiones necesarias
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "plpgsql";

-- Crear schema público si no existe
CREATE SCHEMA IF NOT EXISTS public;

-- =====================================================================================
-- SECUENCIAS
-- =====================================================================================


-- =====================================================================================
-- TABLAS PRINCIPALES DEL DOMINIO ADMINISTRATIVO
-- =====================================================================================

-- Tabla: Sedes principales de la organización
CREATE TABLE public.head_offices (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    code_numeric character varying(50) UNIQUE,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    created_by character varying(255),
    updated_by character varying(255),
    version integer DEFAULT 1 NOT NULL,
    CONSTRAINT head_offices_pkey PRIMARY KEY (id)
);

-- Tabla: Departamentos que pertenecen a las sedes
CREATE TABLE public.departments (
    id uuid NOT NULL,
    head_office_id uuid NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(255),
    code_numeric character varying(50) UNIQUE,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    created_by character varying(255),
    updated_by character varying(255),
    version integer DEFAULT 1 NOT NULL,
    CONSTRAINT departments_pkey PRIMARY KEY (id),
    CONSTRAINT departments_head_office_id_foreign FOREIGN KEY (head_office_id) REFERENCES head_offices(id) ON DELETE CASCADE
);

-- Tabla: Carreras académicas que pertenecen a los departamentos
CREATE TABLE public.careers (
    id uuid NOT NULL,
    department_id uuid NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(255),
    code_numeric character varying(50) UNIQUE,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    created_by character varying(255),
    updated_by character varying(255),
    version integer DEFAULT 1 NOT NULL,
    CONSTRAINT careers_pkey PRIMARY KEY (id),
    CONSTRAINT careers_department_id_foreign FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- Tabla: Subsistemas del sistema de gestión documental
CREATE TABLE public.subsystems (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    code_numeric character varying(50) UNIQUE,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    created_by character varying(255),
    updated_by character varying(255),
    version integer DEFAULT 1 NOT NULL,
    CONSTRAINT subsystems_pkey PRIMARY KEY (id)
);

-- Tabla: Grupos de subsistemas
CREATE TABLE public.subsystem_groups (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    code varchar(100) NOT NULL,
    code_numeric varchar(50),
    description text,
    created_by character varying(255),
    updated_by character varying(255),
    version integer DEFAULT 1 NOT NULL,
    deleted_at timestamp(0) without time zone,
    is_public boolean DEFAULT true NOT NULL, -- indica si es visible a todos o solo interna
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: Relación many-to-many(polimórfica) entre subsistemas y grupos
CREATE TABLE public.subsystem_group_links (
    subsystem_id uuid NOT NULL,
    group_id uuid NOT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (subsystem_id, group_id),
    FOREIGN KEY (subsystem_id) REFERENCES subsystems(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES subsystem_groups(id) ON DELETE CASCADE
);



-- Tabla: Relación many-to-many(polimorfica) entre subsistemas a otras entidades
CREATE TABLE public.subsystem_entity_links (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    subsystem_id uuid NOT NULL REFERENCES subsystems(id) ON DELETE CASCADE,
    entity_type VARCHAR(50) NOT NULL,  -- 'head_office', 'department', 'career', etc.
    entity_id uuid NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =====================================================================================
-- TABLAS DE GESTIÓN DE PROCESOS
-- =====================================================================================

-- Tabla: Categorías de procesos
CREATE TABLE public.process_categories (
    id uuid NOT NULL,
    subsystem_id uuid NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(255),
    numeric_code character varying(50),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    created_by character varying(255),
    updated_by character varying(255),
    deleted_by character varying(255),
    version integer DEFAULT 1 NOT NULL,
    CONSTRAINT process_categories_pkey PRIMARY KEY (id),
    CONSTRAINT process_categories_subsystem_id_foreign FOREIGN KEY (subsystem_id) REFERENCES subsystems(id) ON DELETE CASCADE
);

-- Tabla: Procesos específicos con soporte para jerarquías
CREATE TABLE public.processes (
    id uuid NOT NULL,
    process_category_id uuid NOT NULL,
    parent_id uuid,
    name character varying(255) NOT NULL,
    code character varying(255),
    numeric_code character varying(50),
    "order" integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    created_by character varying(255),
    updated_by character varying(255),
    deleted_by character varying(255),
    version integer DEFAULT 1 NOT NULL,
    CONSTRAINT processes_pkey PRIMARY KEY (id),
    CONSTRAINT chk_order_non_negative CHECK ("order" >= 0),
    CONSTRAINT processes_process_category_id_foreign FOREIGN KEY (process_category_id) REFERENCES process_categories(id) ON DELETE CASCADE,
    CONSTRAINT processes_parent_id_foreign FOREIGN KEY (parent_id) REFERENCES processes(id) ON DELETE CASCADE
);

-- =====================================================================================
-- TABLAS DE GESTIÓN DOCUMENTAL
-- =====================================================================================

-- Tabla: Tipos de documentos
CREATE TABLE public.document_types (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    numeric_code character varying(50),
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    created_by character varying(255),
    updated_by character varying(255),
    version integer DEFAULT 1 NOT NULL,
    CONSTRAINT document_types_pkey PRIMARY KEY (id),
    CONSTRAINT document_types_code_unique UNIQUE (code),
    CONSTRAINT chk_version_positive CHECK (version > 0)
);

-- Tabla: Roles académicos
CREATE TABLE public.academic_roles (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    created_by character varying(255),
    updated_by character varying(255),
    version integer DEFAULT 1 NOT NULL,
    CONSTRAINT academic_roles_pkey PRIMARY KEY (id),
    CONSTRAINT academic_roles_code_unique UNIQUE (code),
    CONSTRAINT chk_version_positive CHECK (version > 0)
);

-- =====================================================================================
-- SISTEMA DE METADATOS AVANZADO (ISO 16175-1)
-- =====================================================================================

-- Tabla: Esquemas de metadatos con versionado y composición (M-N)
CREATE TABLE public.metadata_schemas (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    version integer DEFAULT 1 NOT NULL,
    created_by character varying(255),
    updated_by character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT metadata_schemas_pkey PRIMARY KEY (id)
);

-- Tabla: Diccionario de campos reutilizables
CREATE TABLE public.metadata_fields (
    id uuid NOT NULL,
    field_key character varying(255) NOT NULL,
    label character varying(255) NOT NULL,
    entity_type_id integer, -- referencia a tipo de entidad si aplica (user, faculty, career, etc.)
    type_input_id integer, -- referencia a tipo de input (persona, documental, entidad)
    data_type character varying(255) NOT NULL,  -- referencia a tipo de input (text, number, date, select, etc.)
    created_by character varying(255),
    updated_by character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT metadata_fields_pkey PRIMARY KEY (id),
    CONSTRAINT chk_metadata_fields_valid_data_type CHECK (data_type::text = ANY (ARRAY['string'::character varying, 'integer'::character varying, 'decimal'::character varying, 'date'::character varying, 'boolean'::character varying, 'json'::character varying, 'uuid'::character varying, 'text'::character varying, 'email'::character varying, 'url'::character varying]::text[]))
);

-- Tabla: Relación M-N entre esquemas y campos con reglas específicas
CREATE TABLE public.metadata_schema_fields (
    id uuid NOT NULL,
    metadata_schema_id uuid NOT NULL,
    metadata_field_id uuid NOT NULL,
    is_required boolean DEFAULT false NOT NULL,
    sort_order integer,
    default_value text,
    created_by character varying(255),
    updated_by character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT metadata_schema_fields_pkey PRIMARY KEY (id),
    CONSTRAINT metadata_schema_fields_schema_id_foreign FOREIGN KEY (metadata_schema_id) REFERENCES metadata_schemas(id) ON DELETE CASCADE,
    CONSTRAINT metadata_schema_fields_field_id_foreign FOREIGN KEY (metadata_field_id) REFERENCES metadata_fields(id) ON DELETE CASCADE,
    CONSTRAINT metadata_schema_fields_unique_schema_field UNIQUE (metadata_schema_id, metadata_field_id),
    CONSTRAINT chk_metadata_schema_fields_sort_order_positive CHECK (sort_order IS NULL OR sort_order > 0)
);

-- Tabla: Eventos del sistema de metadatos para auditoría
CREATE TABLE public.metadata_schema_events (
    id uuid NOT NULL,
    schema_id uuid NOT NULL,
    event_type character varying(255) NOT NULL,
    actor_id uuid,
    event_time timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    details jsonb,
    correlation_id uuid,
    external_user_id character varying(255),
    service_version character varying(50),
    CONSTRAINT metadata_schema_events_pkey PRIMARY KEY (id),
    CONSTRAINT metadata_schema_events_schema_id_foreign FOREIGN KEY (schema_id) REFERENCES metadata_schemas(id) ON DELETE CASCADE
);

-- Tabla: Documentos requeridos por proceso con metadatos
CREATE TABLE public.required_documents (
    id uuid NOT NULL,
    process_id uuid,
    document_type_id uuid,
    code_default text DEFAULT 'S/C' NOT NULL,
    name text DEFAULT 'S/N' NOT NULL,
    "order" integer DEFAULT 0 NOT NULL,
    url_resource text,
    is_public boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    created_by character varying(255),
    updated_by character varying(255),
    deleted_by character varying(255),
    metadata_schema_id uuid,
    CONSTRAINT required_documents_pkey PRIMARY KEY (id),
    CONSTRAINT required_documents_document_type_id_foreign FOREIGN KEY (document_type_id) REFERENCES document_types(id) ON DELETE CASCADE,
    CONSTRAINT required_documents_process_id_foreign FOREIGN KEY (process_id) REFERENCES processes(id) ON DELETE CASCADE,
    CONSTRAINT required_documents_metadata_schema_id_foreign FOREIGN KEY (metadata_schema_id) REFERENCES metadata_schemas(id) ON DELETE SET NULL,
    CONSTRAINT chk_required_documents_has_reference CHECK (process_id IS NOT NULL OR metadata_schema_id IS NOT NULL),
    CONSTRAINT chk_order_non_negative CHECK ("order" >= 0)
);

-- =====================================================================================
-- TABLAS DE ALMACENAMIENTO
-- =====================================================================================

-- Tabla: Tipos de unidades de almacenamiento
CREATE TABLE public.storage_unit_types (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT storage_unit_types_pkey PRIMARY KEY (id),
    CONSTRAINT storage_unit_types_code_unique UNIQUE (code)
);

-- Tabla: Unidades de almacenamiento con jerarquía
CREATE TABLE public.storage_units (
    id uuid NOT NULL,
    storage_unit_type_id uuid NOT NULL,
    parent_id uuid,
    can_have_children boolean DEFAULT false,
    department_id uuid NOT NULL,
    label character varying(255) NOT NULL,
    code character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT storage_units_pkey PRIMARY KEY (id),
    CONSTRAINT storage_units_storage_unit_type_id_foreign FOREIGN KEY (storage_unit_type_id) REFERENCES storage_unit_types(id) ON DELETE CASCADE,
    CONSTRAINT storage_units_parent_id_foreign FOREIGN KEY (parent_id) REFERENCES storage_units(id) ON DELETE CASCADE,
    CONSTRAINT storage_units_department_id_foreign FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- =====================================================================================
-- SISTEMA DE AUDITORÍA COMPLETA
-- =====================================================================================

-- Tabla: Registro completo de auditoría
CREATE TABLE public.audit_logs (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    table_name character varying(255) NOT NULL,
    record_id uuid NOT NULL,
    action character varying(50) NOT NULL,
    user_id character varying(255),
    external_user_id character varying(255),
    user_email character varying(255),
    user_name character varying(255),
    ip_address inet,
    user_agent text,
    service_name character varying(100) DEFAULT 'management-document-service'::character varying,
    service_version character varying(50),
    endpoint character varying(500),
    correlation_id uuid,
    session_id character varying(255),
    old_values jsonb,
    new_values jsonb,
    changed_fields text[],
    record_version_before integer,
    record_version_after integer,
    change_reason character varying(500),
    change_metadata jsonb,
    business_context jsonb,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp(0) without time zone,
    CONSTRAINT audit_logs_pkey PRIMARY KEY (id),
    CONSTRAINT chk_valid_action CHECK (action::text = ANY (ARRAY['INSERT'::character varying, 'UPDATE'::character varying, 'DELETE'::character varying, 'SOFT_DELETE'::character varying, 'RESTORE'::character varying, 'BULK_INSERT'::character varying, 'BULK_UPDATE'::character varying, 'BULK_DELETE'::character varying]::text[])),
    CONSTRAINT chk_record_version_logic CHECK (action::text = 'INSERT'::text AND record_version_before IS NULL OR action::text = 'UPDATE'::text AND record_version_before IS NOT NULL AND record_version_after >= record_version_before OR (action::text = ANY (ARRAY['DELETE'::character varying, 'SOFT_DELETE'::character varying]::text[])) AND record_version_before IS NOT NULL OR action::text = 'RESTORE'::text AND record_version_before IS NOT NULL)
);

-- Tabla: Métricas agregadas de auditoría
CREATE TABLE public.audit_metrics (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    metric_name character varying(255) NOT NULL,
    table_name character varying(255),
    action character varying(50),
    user_id character varying(255),
    count_value integer DEFAULT 0,
    sum_value numeric(15,2) DEFAULT 0,
    avg_value numeric(15,2) DEFAULT 0,
    min_value numeric(15,2),
    max_value numeric(15,2),
    period_start timestamp(0) without time zone NOT NULL,
    period_end timestamp(0) without time zone NOT NULL,
    granularity character varying(20) NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    metadata jsonb,
    CONSTRAINT audit_metrics_pkey PRIMARY KEY (id),
    CONSTRAINT chk_period_logic CHECK (period_end > period_start),
    CONSTRAINT chk_positive_counts CHECK (count_value >= 0),
    CONSTRAINT chk_valid_granularity CHECK (granularity::text = ANY (ARRAY['hour'::character varying, 'day'::character varying, 'week'::character varying, 'month'::character varying]::text[]))
);
-- =====================================================================================
-- INBOX AUTH SERVICE
-- =====================================================================================
-- 1) Bandeja de entrada (idempotencia)
CREATE TABLE public.inbox_events (
  id              bigserial PRIMARY KEY,
  topic           text NOT NULL,
  partition       int  NOT NULL,
  offset_value    bigint NOT NULL,
  key             text NULL,
  headers         jsonb NULL,
  payload         jsonb NOT NULL,
  received_at     timestamptz NOT NULL DEFAULT now(),
  processed_at    timestamptz NULL,
  error           text NULL,
  UNIQUE (topic, partition, offset_value)
);

-- 2) Usuarios espejo
CREATE TABLE public.md_auth_users (
  tenant_id       uuid NULL,
  user_id         uuid NOT NULL,
  name            text NULL,
  email           text NULL,
  status          text NULL,
  deleted_at      timestamptz NULL,
  updated_at_src  timestamptz NULL, -- desde snapshot/evento fuente
  updated_at      timestamptz NOT NULL DEFAULT now(),
  PRIMARY KEY (tenant_id, user_id)
);

CREATE INDEX md_auth_users_email_idx ON md_auth_users (email);

-- 3) Permisos (catálogo local por slug)
CREATE TABLE public.md_auth_permissions (
  permission_slug text PRIMARY KEY
);

-- 4) Asignación usuario-permiso (por tenant)
CREATE TABLE public.md_auth_user_permissions (
  tenant_id       uuid NULL,
  user_id         uuid NOT NULL,
  permission_slug text NOT NULL REFERENCES md_auth_permissions(permission_slug) ON DELETE RESTRICT,
  granted_by      uuid NULL,
  reason          text NULL,
  created_at      timestamptz NOT NULL DEFAULT now(),
  PRIMARY KEY (tenant_id, user_id, permission_slug)
);

CREATE INDEX md_auth_user_perm_user_idx ON md_auth_user_permissions (tenant_id, user_id);
CREATE INDEX md_auth_user_perm_perm_idx ON md_auth_user_permissions (permission_slug);
-- =====================================================================================
-- CONFIGURACIÓN DE MICROSERVICIOS
-- =====================================================================================

-- Tabla: Configuración de APIs externas
CREATE TABLE public.external_apis (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    service_name character varying(255) NOT NULL,
    base_url character varying(500) NOT NULL,
    auth_method character varying(50) NOT NULL,
    timeout_seconds integer DEFAULT 30,
    retry_attempts integer DEFAULT 3,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by character varying(255),
    updated_by character varying(255),
    CONSTRAINT external_apis_pkey PRIMARY KEY (id),
    CONSTRAINT external_apis_auth_method_check CHECK (auth_method::text = ANY (ARRAY['bearer'::character varying, 'basic'::character varying, 'api_key'::character varying, 'oauth2'::character varying]::text[])),
    CONSTRAINT external_apis_timeout_seconds_check CHECK (timeout_seconds > 0),
    CONSTRAINT external_apis_retry_attempts_check CHECK (retry_attempts >= 0)
);

-- =====================================================================================
-- FUNCIONES DEFINIDAS POR EL USUARIO
-- =====================================================================================

-- Función para actualizar timestamps automáticamente
CREATE OR REPLACE FUNCTION public.update_updated_at_column()
RETURNS trigger
LANGUAGE plpgsql
AS $function$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$function$;

-- Función para refrescar vista materializada
CREATE OR REPLACE FUNCTION public.refresh_process_hierarchy()
RETURNS void
LANGUAGE plpgsql
AS $function$
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
$function$;

-- Función trigger para refrescar vista automáticamente
CREATE OR REPLACE FUNCTION public.trigger_refresh_process_hierarchy()
RETURNS trigger
LANGUAGE plpgsql
AS $function$
BEGIN
    PERFORM refresh_process_hierarchy();
    RETURN COALESCE(NEW, OLD);
END;
$function$;

-- =====================================================================================
-- TRIGGERS PARA ACTUALIZACIÓN AUTOMÁTICA
-- =====================================================================================

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

-- Triggers para mantenimiento de vistas materializadas
CREATE TRIGGER trigger_processes_refresh_hierarchy
    AFTER INSERT OR UPDATE OR DELETE ON processes
    FOR EACH STATEMENT EXECUTE FUNCTION trigger_refresh_process_hierarchy();

CREATE TRIGGER trigger_process_categories_refresh_hierarchy
    AFTER INSERT OR UPDATE OR DELETE ON process_categories
    FOR EACH STATEMENT EXECUTE FUNCTION trigger_refresh_process_hierarchy();

-- =====================================================================================
-- VISTAS MATERIALIZADAS PARA PERFORMANCE
-- =====================================================================================

-- Vista materializada para jerarquías de procesos
CREATE MATERIALIZED VIEW public.mv_process_hierarchy AS
WITH RECURSIVE process_tree AS (
    -- Nodos raíz (sin padre)
    SELECT
        processes.id,
        processes.parent_id,
        processes.name,
        processes.code,
        processes.process_category_id,
        processes."order",
        0 AS level,
        ARRAY[processes.id] AS path,
        processes.name::text AS full_path
    FROM processes
    WHERE processes.parent_id IS NULL AND processes.deleted_at IS NULL

    UNION ALL

    -- Nodos hijos recursivos
    SELECT
        p.id,
        p.parent_id,
        p.name,
        p.code,
        p.process_category_id,
        p."order",
        pt_1.level + 1,
        pt_1.path || p.id,
        (pt_1.full_path || ' > '::text) || p.name::text AS full_path
    FROM processes p
    JOIN process_tree pt_1 ON p.parent_id = pt_1.id
    WHERE p.deleted_at IS NULL
)
SELECT
    pt.id,
    pt.parent_id,
    pt.name,
    pt.code,
    pt.process_category_id,
    pt."order",
    pt.level,
    pt.path,
    pt.full_path,
    pc.name AS category_name,
    pc.code AS category_code
FROM process_tree pt
JOIN process_categories pc ON pt.process_category_id = pc.id
WHERE pc.deleted_at IS NULL
WITH DATA;

-- =====================================================================================
-- VISTAS PARA CONSULTAS DE AUDITORÍA
-- =====================================================================================

-- Vista para auditoría resumida por usuario
CREATE OR REPLACE VIEW public.v_audit_summary_by_user AS
SELECT
    external_user_id,
    user_name,
    table_name,
    action,
    count(*) AS operation_count,
    min(created_at) AS first_operation,
    max(created_at) AS last_operation,
    array_agg(DISTINCT record_id) AS affected_records
FROM audit_logs
WHERE external_user_id IS NOT NULL
GROUP BY external_user_id, user_name, table_name, action;

-- Vista para cambios recientes
CREATE OR REPLACE VIEW public.v_recent_changes AS
SELECT
    id,
    table_name,
    record_id,
    action,
    external_user_id,
    created_at,
    changed_fields,
    change_metadata ->> 'summary'::text AS change_summary,
    array_length(changed_fields, 1) AS fields_changed_count
FROM audit_logs
WHERE created_at >= (now() - '24:00:00'::interval)
ORDER BY created_at DESC;

-- =====================================================================================
-- ÍNDICES PARA PERFORMANCE
-- =====================================================================================

-- Índices únicos (Primary Keys y Unique Constraints)
CREATE UNIQUE INDEX migrations_pkey ON public.migrations USING btree (id);
CREATE UNIQUE INDEX head_offices_pkey ON public.head_offices USING btree (id);
CREATE UNIQUE INDEX head_offices_code ON public.head_offices USING btree (code) WHERE (deleted_at IS NULL);
CREATE UNIQUE INDEX departments_pkey ON public.departments USING btree (id);
CREATE UNIQUE INDEX careers_pkey ON public.careers USING btree (id);
--CREATE UNIQUE INDEX careers_code ON public.careers USING btree (code) WHERE (deleted_at IS NULL);
CREATE UNIQUE INDEX subsystems_pkey ON public.subsystems USING btree (id);
--CREATE UNIQUE INDEX subsystems_code ON public.subsystems USING btree (code) WHERE (deleted_at IS NULL);
CREATE UNIQUE INDEX careers_subsystems_pkey ON public.careers_subsystems USING btree (career_id, subsystem_id);
CREATE UNIQUE INDEX process_categories_pkey ON public.process_categories USING btree (id);
CREATE UNIQUE INDEX processes_pkey ON public.processes USING btree (id);
CREATE UNIQUE INDEX document_types_pkey ON public.document_types USING btree (id);
CREATE UNIQUE INDEX document_types_code_unique ON public.document_types USING btree (code);
CREATE UNIQUE INDEX academic_roles_pkey ON public.academic_roles USING btree (id);
--CREATE UNIQUE INDEX academic_roles_code_unique ON public.academic_roles USING btree (code);
CREATE UNIQUE INDEX required_documents_pkey ON public.required_documents USING btree (id);
CREATE UNIQUE INDEX storage_unit_types_pkey ON public.storage_unit_types USING btree (id);
--CREATE UNIQUE INDEX storage_unit_types_code_unique ON public.storage_unit_types USING btree (code);
CREATE UNIQUE INDEX storage_units_pkey ON public.storage_units USING btree (id);
CREATE UNIQUE INDEX metadata_schemas_pkey ON public.metadata_schemas USING btree (id);
CREATE UNIQUE INDEX metadata_schemas_name_unique ON public.metadata_schemas USING btree (name);
CREATE UNIQUE INDEX metadata_fields_pkey ON public.metadata_fields USING btree (id);
CREATE UNIQUE INDEX metadata_fields_field_key_unique ON public.metadata_fields USING btree (field_key);
CREATE UNIQUE INDEX metadata_schema_fields_pkey ON public.metadata_schema_fields USING btree (id);
CREATE UNIQUE INDEX metadata_schema_fields_schema_field_unique ON public.metadata_schema_fields USING btree (metadata_schema_id, metadata_field_id);
CREATE UNIQUE INDEX metadata_schema_events_pkey ON public.metadata_schema_events USING btree (id);
CREATE UNIQUE INDEX external_apis_pkey ON public.external_apis USING btree (id);
CREATE UNIQUE INDEX audit_logs_pkey ON public.audit_logs USING btree (id);
CREATE UNIQUE INDEX audit_metrics_pkey ON public.audit_metrics USING btree (id);

-- Índices para consultas de jerarquías activas
CREATE INDEX idx_careers_department_active ON public.careers USING btree (department_id) WHERE (deleted_at IS NULL);
CREATE INDEX idx_processes_category_order ON public.processes USING btree (process_category_id, "order") WHERE (deleted_at IS NULL);
CREATE INDEX idx_required_documents_process_order ON public.required_documents USING btree (process_id, "order") WHERE (deleted_at IS NULL);

-- Índices para sistema de metadatos
CREATE INDEX idx_metadata_schema_fields_schema_order ON public.metadata_schema_fields USING btree (metadata_schema_id, sort_order);
CREATE INDEX idx_metadata_schema_events_correlation ON public.metadata_schema_events USING btree (correlation_id);
CREATE INDEX idx_metadata_schema_events_external_user ON public.metadata_schema_events USING btree (external_user_id);

-- Índices para auditoría
CREATE INDEX idx_document_types_created_by ON public.document_types USING btree (created_by) WHERE (deleted_at IS NULL);
CREATE INDEX idx_audit_logs_table_record ON public.audit_logs USING btree (table_name, record_id);
CREATE INDEX idx_audit_logs_user_time ON public.audit_logs USING btree (external_user_id, created_at DESC);
CREATE INDEX idx_audit_logs_action_time ON public.audit_logs USING btree (action, created_at DESC);
CREATE INDEX idx_audit_logs_correlation ON public.audit_logs USING btree (correlation_id) WHERE (correlation_id IS NOT NULL);
CREATE INDEX idx_audit_logs_service_version ON public.audit_logs USING btree (service_name, service_version);
CREATE INDEX idx_audit_logs_session ON public.audit_logs USING btree (session_id) WHERE (session_id IS NOT NULL);

-- Índices GIN para arrays y JSONB
CREATE INDEX idx_audit_logs_changed_fields ON public.audit_logs USING gin (changed_fields);
CREATE INDEX idx_audit_logs_change_metadata ON public.audit_logs USING gin (change_metadata);
CREATE INDEX idx_audit_logs_business_context ON public.audit_logs USING gin (business_context);

-- Índices para búsquedas por código
CREATE INDEX idx_head_offices_code_active ON public.head_offices USING btree (code) WHERE (deleted_at IS NULL);
CREATE INDEX idx_subsystems_code_active ON public.subsystems USING btree (code) WHERE (deleted_at IS NULL);

-- Índices para required_documents modificado
CREATE INDEX idx_required_documents_metadata_schema ON public.required_documents USING btree (metadata_schema_id) WHERE (metadata_schema_id IS NOT NULL);
CREATE INDEX idx_required_documents_process_nullable ON public.required_documents USING btree (process_id) WHERE (process_id IS NOT NULL);
CREATE INDEX idx_required_documents_type_process_schema ON public.required_documents USING btree (document_type_id, process_id, metadata_schema_id) WHERE (deleted_at IS NULL);

-- Índices para APIs externas
CREATE INDEX idx_external_apis_service_active ON public.external_apis USING btree (service_name) WHERE (is_active = true);

-- Índices para audit_metrics
CREATE INDEX idx_audit_metrics_table_action_period ON public.audit_metrics USING btree (table_name, action, period_start, period_end);
CREATE INDEX idx_audit_metrics_metric_period ON public.audit_metrics USING btree (metric_name, granularity, period_start DESC);
CREATE INDEX idx_audit_metrics_user_period ON public.audit_metrics USING btree (user_id, period_start DESC) WHERE (user_id IS NOT NULL);

-- Índices para vistas materializadas
CREATE INDEX idx_mv_process_hierarchy_category ON public.mv_process_hierarchy USING btree (process_category_id, level);
CREATE INDEX idx_mv_process_hierarchy_parent ON public.mv_process_hierarchy USING btree (parent_id);

-- =====================================================================================
-- COMENTARIOS DESCRIPTIVOS
-- =====================================================================================

-- Comentarios en tablas principales
COMMENT ON TABLE public.audit_logs IS 'Registro completo de auditoría con metadatos detallados para todas las operaciones del sistema';
COMMENT ON TABLE public.audit_metrics IS 'Métricas agregadas de auditoría para reportes, dashboards y análisis de patrones';
COMMENT ON TABLE public.external_apis IS 'Configuración de APIs externas para integración entre microservicios';
COMMENT ON MATERIALIZED VIEW public.mv_process_hierarchy IS 'Vista materializada con jerarquía completa de procesos para consultas optimizadas';

-- Comentarios en columnas críticas
COMMENT ON COLUMN public.audit_logs.table_name IS 'Nombre de la tabla afectada';
COMMENT ON COLUMN public.audit_logs.record_id IS 'ID del registro afectado';
COMMENT ON COLUMN public.audit_logs.action IS 'Tipo de acción: INSERT, UPDATE, DELETE, SOFT_DELETE, RESTORE, BULK_*';
COMMENT ON COLUMN public.audit_logs.correlation_id IS 'ID para trazabilidad distribuida entre microservicios';
COMMENT ON COLUMN public.audit_logs.old_values IS 'Valores anteriores del registro completo (JSON)';
COMMENT ON COLUMN public.audit_logs.new_values IS 'Valores nuevos del registro completo (JSON)';
COMMENT ON COLUMN public.audit_logs.changed_fields IS 'Array de campos que fueron modificados en la operación';
COMMENT ON COLUMN public.audit_logs.record_version_before IS 'Versión del registro antes del cambio';
COMMENT ON COLUMN public.audit_logs.record_version_after IS 'Versión del registro después del cambio';
COMMENT ON COLUMN public.audit_logs.change_metadata IS 'Metadatos detallados del cambio incluyendo diferencias campo por campo';
COMMENT ON COLUMN public.audit_logs.business_context IS 'Contexto de negocio y categorización del tipo de operación';

COMMENT ON COLUMN public.audit_metrics.metric_name IS 'Nombre de la métrica (ej: daily_inserts, user_activity, table_modifications)';
COMMENT ON COLUMN public.audit_metrics.period_start IS 'Inicio del período de agregación de la métrica';
COMMENT ON COLUMN public.audit_metrics.period_end IS 'Fin del período de agregación de la métrica';
COMMENT ON COLUMN public.audit_metrics.granularity IS 'Granularidad temporal: hour, day, week, month';

COMMENT ON COLUMN public.external_apis.service_name IS 'Nombre del microservicio externo';
COMMENT ON COLUMN public.external_apis.auth_method IS 'Método de autenticación: bearer, basic, api_key, oauth2';

COMMENT ON COLUMN public.required_documents.process_id IS 'ID del proceso (nullable) - permite documentos independientes de procesos específicos';
COMMENT ON COLUMN public.required_documents.metadata_schema_id IS 'ID del esquema de metadatos - permite reutilizar esquemas normalizados entre documentos';

COMMENT ON COLUMN public.document_types.created_by IS 'ID usuario externo que creó el registro';
COMMENT ON COLUMN public.document_types.updated_by IS 'ID usuario externo que actualizó el registro';
COMMENT ON COLUMN public.document_types.version IS 'Versión del documento para control de cambios';

COMMENT ON COLUMN public.metadata_schema_events.correlation_id IS 'ID para tracing distribuido entre microservicios';
COMMENT ON COLUMN public.metadata_schema_events.external_user_id IS 'Usuario desde microservicio externo';
COMMENT ON COLUMN public.metadata_schema_events.service_version IS 'Versión del servicio que hizo el cambio';

-- Comentarios en vistas
COMMENT ON VIEW public.v_audit_summary_by_user IS 'Resumen de actividad de auditoría agrupada por usuario para reportes';
COMMENT ON VIEW public.v_recent_changes IS 'Vista de cambios recientes (últimas 24h) para monitoreo en tiempo real';


-- =====================================================================================
-- CONFIGURACIÓN DE PERMISOS (OPCIONAL - AJUSTAR SEGÚN NECESIDADES)
-- =====================================================================================

-- Permisos básicos en schema público
GRANT USAGE, CREATE ON SCHEMA public TO pg_database_owner;
GRANT USAGE ON SCHEMA public TO PUBLIC;

-- Nota: Los permisos de tablas específicas se deben configurar según las necesidades
-- de seguridad de cada implementación. Este script no incluye permisos específicos
-- para evitar conflictos con políticas de seguridad particulares.

-- =====================================================================================
-- FIN DEL SCRIPT DE RECREACIÓN
-- =====================================================================================

-- =====================================================================================
-- INSTRUCCIONES DE USO
-- =====================================================================================
/*
PARA USAR ESTE SCRIPT:

1. CREAR BASE DE DATOS:
   CREATE DATABASE management_db;

2. CONECTAR A LA BASE DE DATOS:
   \c management_db;

3. EJECUTAR ESTE SCRIPT COMPLETO

4. VERIFICAR INSTALACIÓN:
   SELECT table_name FROM information_schema.tables WHERE table_schema = 'public';

5. VERIFICAR DATOS INICIALES:
   SELECT * FROM external_apis;
   SELECT * FROM academic_roles;
   SELECT * FROM metadata_schemas;

CARACTERÍSTICAS IMPLEMENTADAS:
✅ Todas las tablas del dominio con relaciones
✅ Sistema completo de metadatos ISO 16175-1
✅ Sistema de auditoría completa con JSONB
✅ Índices optimizados para performance
✅ Triggers automáticos para timestamps
✅ Vistas materializadas para jerarquías
✅ Vistas de consulta para auditoría
✅ Funciones de mantenimiento
✅ Constraints de integridad completos
✅ Comentarios descriptivos
*/

-- =====================================================================================
-- FIN DEL ARCHIVO
-- =====================================================================================
