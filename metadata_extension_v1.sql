
-- =================================================
--  Gestión documental · módulo de metadatos ISO 16175‑1
--  Incluye soporte para campos relacionales
-- =================================================

-- 1. Plantillas base y herencia
CREATE TABLE metadata_schemas (
    id UUID PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    parent_schema_id UUID REFERENCES metadata_schemas(id) ON DELETE SET NULL,
    is_canonical BOOLEAN DEFAULT FALSE,
    version INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Definición de campos (atributos) de cada plantilla
CREATE TABLE metadata_fields (
    id UUID PRIMARY KEY,
    schema_id UUID REFERENCES metadata_schemas(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    data_type TEXT NOT NULL,             -- STRING | INT | DATE | BOOL | UUID | URI ...
    is_required BOOLEAN DEFAULT FALSE,
    default_value TEXT,
    validation_regex TEXT,
    field_order INT,

    -- === soporte relacional ===
    is_reference BOOLEAN DEFAULT FALSE,  -- TRUE si apunta a otra entidad
    reference_entity TEXT,               -- nombre de la tabla destino
    reference_column TEXT DEFAULT 'id',  -- normalmente la PK 'id'

    -- Consistencia básica
    CONSTRAINT chk_reference
      CHECK (
        (is_reference = FALSE AND reference_entity IS NULL)
        OR
        (is_reference = TRUE  AND reference_entity IS NOT NULL)
      )
);


-- 3. Bitácora de cambios al esquema
CREATE TABLE metadata_schema_events (
    id UUID PRIMARY KEY,
    schema_id UUID REFERENCES metadata_schemas(id) ON DELETE CASCADE,
    event_type TEXT NOT NULL,            -- CREATE | MODIFY | DEPRECATE
    actor_id UUID,
    event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    details JSONB
);

-- 4. Ajuste a required_documents para enlazar plantilla y nivel de seguridad
ALTER TABLE required_documents
    ADD COLUMN schema_id UUID REFERENCES metadata_schemas(id) ON DELETE SET NULL,
    ADD COLUMN security_level TEXT DEFAULT 'PÚBLICO';
