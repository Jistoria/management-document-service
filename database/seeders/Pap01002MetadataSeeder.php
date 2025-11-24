<?php

namespace Database\Seeders;

use App\Constants\EntityType;
use App\Constants\TypeInput;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Pap01002MetadataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        /**
         * Crea (si no existe) un metadata_field y devuelve su UUID.
         */
        $getOrCreateField = function (
            string $fieldKey,
            string $label,
            string $dataType = 'string',
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
                'id'             => $id,
                'field_key'      => $fieldKey,
                'label'          => $label,
                'entity_type_id' => $entityTypeId,
                'type_input_id'  => $typeInputId,
                'data_type'      => $dataType,
                'created_by'     => 'system',
                'updated_by'     => 'system',
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            return $id;
        };

        // 1) Campos reutilizables con entity_type_id y type_input_id
        // PERSON y ENTITY usan 'json' porque reciben objetos completos del microservicio
        $authorId = $getOrCreateField(
            'author',
            'Autor (estudiante)',
            'json', // Se guarda el objeto completo del usuario
            EntityType::USER,
            TypeInput::PERSON
        );

        $tutorId = $getOrCreateField(
            'tutor',
            'Tutor académico',
            'json', // Se guarda el objeto completo del usuario
            EntityType::USER,
            TypeInput::PERSON
        );

        $facultyId = $getOrCreateField(
            'faculty',
            'Facultad',
            'json', // Se guarda el objeto completo de la facultad
            EntityType::FACULTY,
            TypeInput::ENTITY
        );

        $careerId = $getOrCreateField(
            'career',
            'Carrera',
            'json', // Se guarda el objeto completo de la carrera
            EntityType::CAREER,
            TypeInput::ENTITY
        );

        // DOCUMENT usa tipos simples porque son valores directos, no referencias
        $academicPeriodId = $getOrCreateField(
            'academic_period',
            'Periodo académico',
            'string', // Valor simple de texto
            null,
            TypeInput::DOCUMENT
        );

        // 2) Esquema para PAP-01-002
        $schemaName = 'PAP-01-002 Registro Actividades Diarias del Estudiante';

        $schema = DB::table('metadata_schemas')
            ->where('name', $schemaName)
            ->first();

        if (! $schema) {
            $schemaId = (string) Str::uuid();

            DB::table('metadata_schemas')->insert([
                'id'          => $schemaId,
                'name'        => $schemaName,
                'description' => 'Metadatos para el formato PAP-01-002 Registro Actividades Diarias del Estudiante',
                'version'     => 1,
                'created_by'  => 'system',
                'updated_by'  => 'system',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        } else {
            $schemaId = $schema->id;
        }

        // 3) Relación esquema-campos (ordenados y requeridos)
        $sortOrder = 1;
        $fieldIds = [
            $authorId,
            $tutorId,
            $facultyId,
            $careerId,
            $academicPeriodId,
        ];

        foreach ($fieldIds as $fieldId) {
            $exists = DB::table('metadata_schema_fields')
                ->where('metadata_schema_id', $schemaId)
                ->where('metadata_field_id', $fieldId)
                ->exists();

            if (! $exists) {
                DB::table('metadata_schema_fields')->insert([
                    'id'                  => (string) Str::uuid(),
                    'metadata_schema_id'  => $schemaId,
                    'metadata_field_id'   => $fieldId,
                    'is_required'         => true,
                    'sort_order'          => $sortOrder++,
                    'default_value'       => null,
                    'created_by'          => 'system',
                    'updated_by'          => 'system',
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }
        }
    }
}
