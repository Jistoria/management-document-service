<?php

namespace Database\Seeders;

use App\Constants\EntityType;
use App\Constants\MetadataFieldDataType;
use App\Constants\TypeInput;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UniversityMetadataFieldsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $getOrCreateField = function (
            string $fieldKey,
            string $label,
            string $dataType = MetadataFieldDataType::STRING,
            ?int $entityTypeId = null,
            ?int $typeInputId = null
        ) use ($now) {
            $existing = DB::table('metadata_fields')
                ->where('field_key', $fieldKey)
                ->first();

            if ($existing) {
                return $existing->id;
            }

            $id = (string) Str::uuid();

            DB::table('metadata_fields')->insert([
                'id' => $id,
                'field_key' => $fieldKey,
                'label' => $label,
                'entity_type_id' => $entityTypeId,
                'type_input_id' => $typeInputId,
                'data_type' => $dataType,
                'created_by' => 'system',
                'updated_by' => 'system',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $id;
        };

        $fields = [
            ['document_title', 'Título del documento', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['document_subtitle', 'Subtítulo del documento', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['document_number', 'Número de documento', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['document_date', 'Fecha del documento', MetadataFieldDataType::DATE, null, TypeInput::DOCUMENT],
            ['issued_date', 'Fecha de emisión', MetadataFieldDataType::DATE, null, TypeInput::DOCUMENT],
            ['received_date', 'Fecha de recepción', MetadataFieldDataType::DATE, null, TypeInput::DOCUMENT],
            ['subject', 'Asunto', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['keywords', 'Palabras clave', MetadataFieldDataType::TEXT, null, TypeInput::DOCUMENT],
            ['summary', 'Resumen', MetadataFieldDataType::TEXT, null, TypeInput::DOCUMENT],
            ['language', 'Idioma', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['format', 'Formato', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['file_size', 'Tamaño del archivo (KB)', MetadataFieldDataType::INTEGER, null, TypeInput::DOCUMENT],
            ['checksum', 'Checksum', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['folio_count', 'Número de folios', MetadataFieldDataType::INTEGER, null, TypeInput::DOCUMENT],
            ['version', 'Versión', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['status', 'Estado', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['access_level', 'Nivel de acceso', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['confidentiality_level', 'Nivel de confidencialidad', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['retention_period', 'Periodo de retención', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['retention_disposition', 'Disposición final', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['storage_location', 'Ubicación de almacenamiento', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['classification_code', 'Código de clasificación', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['series', 'Serie documental', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['subseries', 'Subserie documental', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['origin_unit', 'Unidad productora', MetadataFieldDataType::STRING, null, TypeInput::DOCUMENT],
            ['document_type', 'Tipo de documento', MetadataFieldDataType::JSON, EntityType::DOCUMENT, TypeInput::ENTITY],
            ['process', 'Proceso', MetadataFieldDataType::JSON, EntityType::PROCESS, TypeInput::ENTITY],
            ['faculty', 'Facultad', MetadataFieldDataType::JSON, EntityType::FACULTY, TypeInput::ENTITY],
            ['career', 'Carrera', MetadataFieldDataType::JSON, EntityType::CAREER, TypeInput::ENTITY],
            ['department', 'Departamento', MetadataFieldDataType::JSON, EntityType::DEPARTMENT, TypeInput::ENTITY],
            ['academic_role', 'Rol académico', MetadataFieldDataType::JSON, EntityType::ACADEMIC_ROLE, TypeInput::ENTITY],
            ['author', 'Autor', MetadataFieldDataType::JSON, EntityType::USER, TypeInput::PERSON],
            ['creator', 'Creador', MetadataFieldDataType::JSON, EntityType::USER, TypeInput::PERSON],
            ['reviewer', 'Revisor', MetadataFieldDataType::JSON, EntityType::USER, TypeInput::PERSON],
            ['approver', 'Aprobador', MetadataFieldDataType::JSON, EntityType::USER, TypeInput::PERSON],
            ['recipient', 'Destinatario', MetadataFieldDataType::JSON, EntityType::USER, TypeInput::PERSON],
            ['external_contact', 'Contacto externo', MetadataFieldDataType::JSON, EntityType::PERSON, TypeInput::PERSON],
        ];

        foreach ($fields as $field) {
            $getOrCreateField(...$field);
        }
    }
}
