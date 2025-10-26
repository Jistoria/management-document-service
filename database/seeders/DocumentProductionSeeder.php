<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;
use App\Models\MetadataSchema;
use App\Models\MetadataField;
use App\Models\RequiredDocument;
use App\Models\Process;

class DocumentProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📚 Creando datos de documentos para producción...');

        $this->createDocumentTypes();
        $this->createMetadataSchemasAndFields();
        $this->createRequiredDocuments();

        $this->command->info("   ✓ Document Types: " . DocumentType::count());
        $this->command->info("   ✓ Metadata Schemas: " . MetadataSchema::count());
        $this->command->info("   ✓ Metadata Fields: " . MetadataField::count());
        $this->command->info("   ✓ Required Documents: " . RequiredDocument::count());
    }

    private function createDocumentTypes(): void
    {
        $documentTypes = [
            // Documentos académicos
            ['name' => 'Acta de Grado', 'code' => 'ACTA_GRADO'],
            ['name' => 'Certificado de Estudio', 'code' => 'CERT_ESTUDIO'],
            ['name' => 'Constancia de Matrícula', 'code' => 'CONST_MATRICULA'],
            ['name' => 'Diploma', 'code' => 'DIPLOMA'],
            ['name' => 'Transcript Académico', 'code' => 'TRANSCRIPT'],

            // Documentos de investigación
            ['name' => 'Proyecto de Investigación', 'code' => 'PROY_INVEST'],
            ['name' => 'Informe de Investigación', 'code' => 'INF_INVEST'],
            ['name' => 'Artículo Científico', 'code' => 'ART_CIENTIFICO'],
            ['name' => 'Tesis de Grado', 'code' => 'TESIS'],
            ['name' => 'Trabajo de Grado', 'code' => 'TRAB_GRADO'],

            // Documentos administrativos
            ['name' => 'Resolución', 'code' => 'RESOLUCION'],
            ['name' => 'Memorando', 'code' => 'MEMORANDO'],
            ['name' => 'Circular', 'code' => 'CIRCULAR'],
            ['name' => 'Acta de Reunión', 'code' => 'ACTA_REUNION'],
            ['name' => 'Informe Administrativo', 'code' => 'INF_ADMIN'],

            // Documentos de laboratorio
            ['name' => 'Protocolo de Laboratorio', 'code' => 'PROT_LAB'],
            ['name' => 'Informe de Laboratorio', 'code' => 'INF_LAB'],
            ['name' => 'Manual de Procedimientos', 'code' => 'MAN_PROC'],

            // Documentos de prácticas
            ['name' => 'Convenio de Práctica', 'code' => 'CONV_PRACTICA'],
            ['name' => 'Informe de Práctica', 'code' => 'INF_PRACTICA'],
            ['name' => 'Evaluación de Práctica', 'code' => 'EVAL_PRACTICA'],
        ];

        foreach ($documentTypes as $docTypeData) {
            DocumentType::firstOrCreate([
                'code' => $docTypeData['code']
            ], $docTypeData);
        }
    }

    private function createMetadataSchemasAndFields(): void
    {
        $schemas = [
            [
                'data' => [
                    'name' => 'Esquema Básico de Documento',
                    'description' => 'Esquema básico aplicable a cualquier documento del sistema',
                    'version' => 1,
                    'is_canonical' => true,
                ],
                'fields' => [
                    ['name' => 'titulo', 'data_type' => 'string', 'is_required' => true, 'field_order' => 1],
                    ['name' => 'fecha', 'data_type' => 'date', 'is_required' => true, 'field_order' => 2],
                    ['name' => 'descripcion', 'data_type' => 'string', 'is_required' => false, 'field_order' => 3],
                    ['name' => 'codigo_referencia', 'data_type' => 'string', 'is_required' => false, 'field_order' => 4],
                ],
            ],
            [
                'data' => [
                    'name' => 'Esquema de Trabajo de Grado',
                    'description' => 'Esquema específico para trabajos de grado y tesis',
                    'version' => 1,
                    'is_canonical' => true,
                ],
                'fields' => [
                    ['name' => 'titulo', 'data_type' => 'string', 'is_required' => true, 'field_order' => 1],
                    ['name' => 'autor', 'data_type' => 'string', 'is_required' => true, 'field_order' => 2],
                    ['name' => 'tutor', 'data_type' => 'string', 'is_required' => true, 'field_order' => 3],
                    ['name' => 'fecha_presentacion', 'data_type' => 'date', 'is_required' => true, 'field_order' => 4],
                    ['name' => 'palabras_clave', 'data_type' => 'string', 'is_required' => false, 'field_order' => 5],
                ],
            ],
            [
                'data' => [
                    'name' => 'Esquema de Investigación',
                    'description' => 'Esquema para proyectos e informes de investigación',
                    'version' => 1,
                    'is_canonical' => true,
                ],
                'fields' => [
                    ['name' => 'nombre_proyecto', 'data_type' => 'string', 'is_required' => true, 'field_order' => 1],
                    ['name' => 'investigador_principal', 'data_type' => 'string', 'is_required' => true, 'field_order' => 2],
                    ['name' => 'fecha_inicio', 'data_type' => 'date', 'is_required' => true, 'field_order' => 3],
                    ['name' => 'fecha_fin', 'data_type' => 'date', 'is_required' => false, 'field_order' => 4],
                    ['name' => 'presupuesto', 'data_type' => 'decimal', 'is_required' => false, 'field_order' => 5],
                ],
            ],
        ];

        foreach ($schemas as $schema) {
            $schemaModel = MetadataSchema::firstOrCreate(
                ['name' => $schema['data']['name']],
                $schema['data']
            );

            foreach ($schema['fields'] as $fieldData) {
                MetadataField::firstOrCreate(
                    [
                        'schema_id' => $schemaModel->id,
                        'name' => $fieldData['name']
                    ],
                    array_merge($fieldData, ['schema_id' => $schemaModel->id])
                );
            }
        }
    }

    private function createRequiredDocuments(): void
    {
        $requirements = [
            ['process_code' => 'INSCRIPCION', 'doc_code' => 'CONST_MATRICULA', 'schema_name' => 'Esquema Básico de Documento', 'order' => 1],
            ['process_code' => 'GRADUACION', 'doc_code' => 'TESIS', 'schema_name' => 'Esquema de Trabajo de Grado', 'order' => 1],
            ['process_code' => 'GRADUACION', 'doc_code' => 'ACTA_GRADO', 'schema_name' => 'Esquema Básico de Documento', 'order' => 2],
            ['process_code' => 'REG_PROYECTO', 'doc_code' => 'PROY_INVEST', 'schema_name' => 'Esquema de Investigación', 'order' => 1],
        ];

        foreach ($requirements as $req) {
            $process = Process::where('code', $req['process_code'])->first();
            $docType = DocumentType::where('code', $req['doc_code'])->first();
            $schema = MetadataSchema::where('name', $req['schema_name'])->first();

            if ($process && $docType && $schema) {
                RequiredDocument::firstOrCreate(
                    [
                        'process_id' => $process->id,
                        'document_type_id' => $docType->id,
                        'metadata_schema_id' => $schema->id,
                        'code_default' => $req['process_code'] . ' - ' . $req['doc_code']. 'V1',
                        'name' => $req['process_code'] . ' - ' . $req['doc_code']. 'V1',
                    ],
                    [
                        'process_id' => $process->id,
                        'document_type_id' => $docType->id,
                        'metadata_schema_id' => $schema->id,
                        'code_default' => $req['process_code'] . ' - ' . $req['doc_code'].'V2',
                        'name' => $req['process_code'] . ' - ' . $req['doc_code'].'V2',
                    ]
                );
            }
        }
    }
}

