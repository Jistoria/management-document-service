-- === Extensiones necesarias ===
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- === Sedes ===
CREATE TABLE head_offices (
    id UUID PRIMARY KEY,
    name TEXT NOT NULL
);

-- === Unidades (Facultades, Direcciones) ===
CREATE TABLE departments (
    id UUID PRIMARY KEY,
    head_office_id UUID REFERENCES head_offices(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    type TEXT NOT NULL, -- FACULTAD, DIRECCION, etc.
    code_document TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- === Carreras o Áreas ===
CREATE TABLE careers (
    id UUID PRIMARY KEY,
    department_id UUID REFERENCES departments(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    code_document TEXT UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- === Subsistemas ===
CREATE TABLE subsystems (
    id UUID PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    code_document TEXT UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- === Carreras <-> Subsistemas (N:M) ===
CREATE TABLE careers_subsystems (
    career_id UUID REFERENCES careers(id) ON DELETE CASCADE,
    subsystem_id UUID REFERENCES subsystems(id) ON DELETE CASCADE,
    PRIMARY KEY (career_id, subsystem_id)
);

-- === Categorías de Proceso ===
CREATE TABLE process_categories (
    id UUID PRIMARY KEY,
    subsystem_id UUID REFERENCES subsystems(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- === Procesos ===
CREATE TABLE processes (
    id UUID PRIMARY KEY,
    process_category_id UUID REFERENCES process_categories(id) ON DELETE SET NULL,
    name TEXT NOT NULL,
    code_document TEXT UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- === Tipos de documento (series/subseries) ===
CREATE TABLE document_types (
    id UUID PRIMARY KEY,
    name TEXT NOT NULL,
    code TEXT NOT NULL
);

-- === Roles académicos ===
CREATE TABLE academic_roles (
    id UUID PRIMARY KEY,
    name TEXT NOT NULL
);

-- === Documentos requeridos ===
CREATE TABLE required_documents (
    id UUID PRIMARY KEY,
    academic_role_id UUID REFERENCES academic_roles(id),
    process_id UUID REFERENCES processes(id) ON DELETE SET NULL,
    document_type_id UUID REFERENCES document_types(id) ON DELETE SET NULL,
    parent_document_id UUID REFERENCES required_documents(id) ON DELETE SET NULL,
    name TEXT NOT NULL,
    code_document TEXT NOT NULL,
    url_template TEXT,
    order_index INTEGER,
    is_auditable BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- === Períodos Académicos ===
CREATE TABLE academic_periods (
    id UUID PRIMARY KEY,
    name TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL
);

-- === Roles del sistema interno ===
CREATE TABLE system_roles (
    id UUID PRIMARY KEY,
    name TEXT NOT NULL
);

-- === Usuarios <-> Roles del sistema ===
CREATE TABLE users_system_roles (
    user_id UUID NOT NULL,
    system_role_id UUID REFERENCES system_roles(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, system_role_id)
);

CREATE TABLE record_events (
    id UUID PRIMARY KEY,
    document_id UUID REFERENCES required_documents(id),
    event_type TEXT NOT NULL,          -- CREATED, VIEWED, MODIFIED, DISPOSED, etc.
    event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actor_id UUID,
    old_value JSONB,
    new_value JSONB
);


-- =========================================================
-- 1. Catálogo de tipos de contenedor
-- =========================================================
CREATE TABLE storage_unit_types (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL UNIQUE       -- BODEGA, PERCHA, CAJA, SECCION, CARPETA, …
);

INSERT INTO storage_unit_types (name)
VALUES ('BODEGA'), ('PERCHA'), ('CAJA'), ('SECCION'), ('CARPETA');

-- =========================================================
-- 2. Contenedores físicos con jerarquía + morph
-- =========================================================
CREATE TABLE storage_units (
    id UUID PRIMARY KEY,
    parent_id UUID REFERENCES storage_units(id) ON DELETE CASCADE,
    type_id  INT  REFERENCES storage_unit_types(id) NOT NULL,

    -- === anclaje polimórfico estilo Laravel ===
    anchorable_id   UUID,           -- PK del campus, unit o career
    anchorable_type TEXT,           -- 'campuses', 'units', 'careers', …

    name TEXT NOT NULL,
    code TEXT,
    metadata JSONB DEFAULT '{}',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Índice compuesto para resolver rápido el morph
CREATE INDEX idx_storage_units_anchor
          ON storage_units (anchorable_type, anchorable_id);
