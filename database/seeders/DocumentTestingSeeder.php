<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;
use App\Models\MetadataSchema;
use App\Models\MetadataField;
use App\Models\RequiredDocument;
use App\Models\ProcessCategory;
use App\Models\Process;
use App\Models\Subsystem;

class DocumentTestingSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📚 Creando datos de documentos para testing...');

        $this->createDocumentTypes();
        $this->createMetadataSchemasAndFields();
        $this->createProcessAndRequiredDocuments();

        $this->command->info("   ✓ Document Types: " . DocumentType::count());
        $this->command->info("   ✓ Metadata Schemas: " . MetadataSchema::count());
        $this->command->info("   ✓ Metadata Fields: " . MetadataField::count());
        $this->command->info("   ✓ Required Documents: " . RequiredDocument::count());
    }

    private function createDocumentTypes(): void
    {
        $documentTypes = [
            ['name' => 'Acta', 'code' => 'ACTA'],
            ['name' => 'Certificado', 'code' => 'CERT'],
            ['name' => 'Proyecto', 'code' => 'PROY'],
            ['name' => 'Informe', 'code' => 'INF'],
        ];

        foreach ($documentTypes as $docType) {
            DocumentType::create($docType);
        }
    }

    private function createMetadataSchemasAndFields(): void
    {
        $schemas = [
            [
                'data' => [
                    'name' => 'Esquema de Proyecto de Grado',
                    'description' => 'Esquema para metadatos de proyectos de grado',
                    'version' => 1,
                    'is_canonical' => true,
                ],
                'fields' => [
                    ['name' => 'titulo', 'data_type' => 'string', 'is_required' => true, 'field_order' => 1],
                    ['name' => 'estudiante', 'data_type' => 'string', 'is_required' => true, 'field_order' => 2],
                    ['name' => 'fecha_presentacion', 'data_type' => 'date', 'is_required' => true, 'field_order' => 3],
                    ['name' => 'tutor', 'data_type' => 'string', 'is_required' => false, 'field_order' => 4],
                ],
            ],
            [
                'data' => [
                    'name' => 'Esquema de Certificación',
                    'description' => 'Esquema para metadatos de certificaciones',
                    'version' => 1,
                    'is_canonical' => true,
                ],
                'fields' => [
                    ['name' => 'nombre_certificado', 'data_type' => 'string', 'is_required' => true, 'field_order' => 1],
                    ['name' => 'fecha_emision', 'data_type' => 'date', 'is_required' => true, 'field_order' => 2],
                    ['name' => 'autoridad', 'data_type' => 'string', 'is_required' => false, 'field_order' => 3],
                ],
            ],
        ];

        foreach ($schemas as $schema) {
            $schemaModel = MetadataSchema::create($schema['data']);
            foreach ($schema['fields'] as $fieldData) {
                MetadataField::create(array_merge($fieldData, ['schema_id' => $schemaModel->id]));
            }
        }
    }

    private function createProcessAndRequiredDocuments(): void
    {
        $subsystem = Subsystem::where('code', 'PROJ')->first();

        $category = ProcessCategory::create([
            'subsystem_id' => $subsystem?->id,
            'name' => 'Procesos de Documentos de Prueba',
            'code' => 'DOC_TEST',
        ]);

        $process = Process::create([
            'process_category_id' => $category->id,
            'name' => 'Registro de Documento de Prueba',
            'code' => 'REG_DOC_TEST',
            'order' => 1,
        ]);

        $requirements = [
            ['doc_code' => 'PROY', 'schema_name' => 'Esquema de Proyecto de Grado', 'order' => 1, 'mandatory' => true],
            ['doc_code' => 'CERT', 'schema_name' => 'Esquema de Certificación', 'order' => 2, 'mandatory' => false],
        ];

        foreach ($requirements as $req) {
            $docType = DocumentType::where('code', $req['doc_code'])->first();
            $schema = MetadataSchema::where('name', $req['schema_name'])->first();

            if ($docType && $schema) {
                RequiredDocument::create([
                    'process_id' => $process->id,
                    'document_type_id' => $docType->id,
                    'metadata_schema_id' => $schema->id,
                    'order' => $req['order'],
                    'mandatory' => $req['mandatory'],
                ]);
            }
        }
    }
}

