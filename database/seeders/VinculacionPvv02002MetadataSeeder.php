<?php

namespace Database\Seeders;

use App\Constants\EntityType;
use App\Constants\MetadataFieldDataType;
use App\Constants\TypeInput;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeder de metadatos para el documento PVV-02-F-002 (Informe semestral de vinculación).
 *
 * Este seeder registra los campos de metadatos identificados en la tesis para el
 * informe semestral del líder del proyecto de vinculación (secciones A y B),
 * crea el esquema correspondiente y lo asocia a los documentos de tipo PVV-02-F-002
 * cuando existan en la tabla `required_documents`.
 */
class VinculacionPvv02002MetadataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Función de ayuda para crear o reutilizar campos de metadatos.
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

        // ============================================================
        // 1) Crear campos de la sección A: Identidad del documento
        // ============================================================
        $documentCodeId = $getOrCreateField(
            'document_code',
            'Código del documento',
            MetadataFieldDataType::STRING,
            null,
            TypeInput::DOCUMENT
        );

        $documentVersionId = $getOrCreateField(
            'document_version',
            'Versión del documento',
            MetadataFieldDataType::INTEGER,
            null,
            TypeInput::DOCUMENT
        );

        $documentTitleId = $getOrCreateField(
            'document_title',
            'Nombre del documento',
            MetadataFieldDataType::STRING,
            null,
            TypeInput::DOCUMENT
        );

        $reportMonthYearId = $getOrCreateField(
            'report_month_year',
            'Mes/Año del informe',
            MetadataFieldDataType::STRING,
            null,
            TypeInput::DOCUMENT
        );

        // ============================================================
        // 2) Crear campos de la sección B: Información general del proyecto
        // ============================================================
        $projectNameId = $getOrCreateField(
            'project_name',
            'Nombre del proyecto',
            MetadataFieldDataType::STRING,
            null,
            TypeInput::DOCUMENT
        );

        $academicUnitId = $getOrCreateField(
            'academic_unit',
            'Unidad académica',
            MetadataFieldDataType::STRING,
            null,
            TypeInput::DOCUMENT
        );

        $projectCodeId = $getOrCreateField(
            'project_code',
            'Código de proyecto',
            MetadataFieldDataType::STRING,
            null,
            TypeInput::DOCUMENT
        );

        $leaderNameId = $getOrCreateField(
            'leader_name',
            'Docente líder del proyecto',
            MetadataFieldDataType::STRING,
            null,
            TypeInput::DOCUMENT
        );

        $beneficiaryEntityId = $getOrCreateField(
            'beneficiary_entity',
            'Entidad beneficiaria',
            MetadataFieldDataType::STRING,
            null,
            TypeInput::DOCUMENT
        );

        $projectStartDateId = $getOrCreateField(
            'project_start_date',
            'Inicio de vigencia',
            MetadataFieldDataType::DATE,
            null,
            TypeInput::DOCUMENT
        );

        $projectEndDateId = $getOrCreateField(
            'project_end_date',
            'Fin de vigencia',
            MetadataFieldDataType::DATE,
            null,
            TypeInput::DOCUMENT
        );

        $directBeneficiariesId = $getOrCreateField(
            'direct_beneficiaries_count',
            'Beneficiarios directos (número)',
            MetadataFieldDataType::INTEGER,
            null,
            TypeInput::DOCUMENT
        );

        $indirectBeneficiariesId = $getOrCreateField(
            'indirect_beneficiaries_count',
            'Beneficiarios indirectos (número)',
            MetadataFieldDataType::INTEGER,
            null,
            TypeInput::DOCUMENT
        );

        $researchLineId = $getOrCreateField(
            'research_line',
            'Línea de investigación',
            MetadataFieldDataType::STRING,
            null,
            TypeInput::DOCUMENT
        );

        $odsId = $getOrCreateField(
            'ods',
            'Objetivo de Desarrollo Sostenible (ODS)',
            MetadataFieldDataType::TEXT,
            null,
            TypeInput::DOCUMENT
        );

        // Utilizamos el campo "career" ya existente como una entidad (json).
        $careerId = $getOrCreateField(
            'career',
            'Carrera',
            MetadataFieldDataType::JSON,
            EntityType::CAREER,
            TypeInput::ENTITY
        );

        // ============================================================
        // 3) Crear o recuperar el esquema de metadatos
        // ============================================================
        $schemaName = 'PVV-02-F-002 | Informe semestral líder de proyecto de vinculación';

        $schema = DB::table('metadata_schemas')
            ->where('name', $schemaName)
            ->first();

        if (! $schema) {
            $schemaId = (string) Str::uuid();

            DB::table('metadata_schemas')->insert([
                'id' => $schemaId,
                'name' => $schemaName,
                'description' => 'Esquema de metadatos para el informe semestral del líder de proyecto de vinculación (PVV-02-F-002).',
                'version' => 1,
                'created_by' => 'system',
                'updated_by' => 'system',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $schemaId = $schema->id;
        }

        // ============================================================
        // 4) Asociar campos al esquema en el orden correcto
        // ============================================================
        $fieldIds = [
            $documentCodeId,
            $documentVersionId,
            $documentTitleId,
            $reportMonthYearId,
            $projectNameId,
            $academicUnitId,
            $projectCodeId,
            $leaderNameId,
            $beneficiaryEntityId,
            $projectStartDateId,
            $projectEndDateId,
            $directBeneficiariesId,
            $indirectBeneficiariesId,
            $researchLineId,
            $odsId,
            $careerId,
        ];

        $sortOrder = 1;
        foreach ($fieldIds as $fieldId) {
            $exists = DB::table('metadata_schema_fields')
                ->where('metadata_schema_id', $schemaId)
                ->where('metadata_field_id', $fieldId)
                ->exists();

            if (! $exists) {
                $isRequired = true;
                DB::table('metadata_schema_fields')->insert([
                    'id' => (string) Str::uuid(),
                    'metadata_schema_id' => $schemaId,
                    'metadata_field_id' => $fieldId,
                    'is_required' => $isRequired,
                    'is_repeatable' => false,
                    'min_occurs' => $isRequired ? 1 : 0,
                    'max_occurs' => 1,
                    'allow_duplicates' => true,
                    'sort_order' => $sortOrder++,
                    'default_value' => null,
                    'created_by' => 'system',
                    'updated_by' => 'system',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ============================================================
        // 5) Vincular el esquema a los documentos PVV-02-F-002 (si existen)
        // ============================================================
        $requiredDocument = DB::table('required_documents')
            ->where('code_default', 'PVV-02-F-002')
            ->first();

        if ($requiredDocument && ! $requiredDocument->metadata_schema_id) {
            DB::table('required_documents')
                ->where('id', $requiredDocument->id)
                ->update([
                    'metadata_schema_id' => $schemaId,
                    'updated_at' => $now,
                    'updated_by' => 'system',
                ]);
        }
    }
}