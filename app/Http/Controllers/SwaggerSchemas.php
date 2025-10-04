<?php

namespace App\Http\Controllers;

/**
 * @OA\Schema(
 *     schema="HeadOffice",
 *     type="object",
 *     title="Head Office",
 *     description="Sede de la organización",
 *     @OA\Property(property="id", type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2", description="ID único de la sede"),
 *     @OA\Property(property="name", type="string", example="Sede Central", description="Nombre de la sede"),
 *     @OA\Property(property="code", type="string", example="CENTRAL", description="Código único de la sede"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2025-07-04T22:36:25.000000Z", description="Fecha de creación"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2025-07-04T22:36:47.000000Z", description="Fecha de última actualización"),
 *     @OA\Property(property="createdBy", type="string", example="system", description="Usuario que creó la sede"),
 *     @OA\Property(property="updatedBy", type="string", example="system", description="Usuario que actualizó la sede"),
 *     @OA\Property(property="version", type="integer", example=2, description="Versión del registro"),
 *     @OA\Property(property="departmentsCount", type="integer", example=0, description="Número de departamentos asociados")
 * )
 *
 * @OA\Schema(
 *     schema="HeadOfficeDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/HeadOffice"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="departments",
 *                 type="object",
 *                 description="Departamentos asociados (incluido condicionalmente)"
 *             ),
 *             @OA\Property(
 *                 property="statistics",
 *                 type="object",
 *                 description="Estadísticas de la sede (incluido condicionalmente)"
 *             ),
 *             @OA\Property(
 *                 property="hierarchy",
 *                 type="object",
 *                 description="Jerarquía completa (incluido condicionalmente)"
 *             ),
 *             @OA\Property(
 *                 property="meta",
 *                 type="object",
 *                 @OA\Property(property="resourceType", type="string", example="head_office"),
 *                 @OA\Property(property="generatedAt", type="string", format="date-time"),
 *                 @OA\Property(property="context", type="array", @OA\Items(type="string"))
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="HeadOfficeHierarchy",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/HeadOffice"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="departments",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="string", format="uuid"),
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="code", type="string"),
 *                     @OA\Property(
 *                         property="careers",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="id", type="string", format="uuid"),
 *                             @OA\Property(property="name", type="string"),
 *                             @OA\Property(property="code", type="string")
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="DocumentType",
 *     type="object",
 *     title="Document Type",
 *     description="Tipo de documento del sistema",
 *     @OA\Property(property="id", type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2", description="ID único del tipo de documento"),
 *     @OA\Property(property="name", type="string", example="Certificado Académico", description="Nombre del tipo de documento"),
 *     @OA\Property(property="code", type="string", example="CERT_ACAD", description="Código único del tipo de documento"),
 *     @OA\Property(property="description", type="string", example="Certificado de estudios académicos", description="Descripción del tipo de documento"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2025-07-04T22:36:25.000000Z", description="Fecha de creación"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2025-07-04T22:36:47.000000Z", description="Fecha de última actualización"),
 *     @OA\Property(property="createdBy", type="string", example="system", description="Usuario que creó el tipo de documento"),
 *     @OA\Property(property="updatedBy", type="string", example="system", description="Usuario que actualizó el tipo de documento"),
 *     @OA\Property(property="version", type="integer", example=1, description="Versión del registro"),
 *     @OA\Property(property="requiredDocumentsCount", type="integer", example=5, description="Número de documentos requeridos asociados")
 * )
 *
 * @OA\Schema(
 *     schema="DocumentTypeDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/DocumentType"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="requiredDocuments",
 *                 type="object",
 *                 description="Documentos requeridos asociados (incluido condicionalmente)"
 *             ),
 *             @OA\Property(
 *                 property="statistics",
 *                 type="object",
 *                 description="Estadísticas del tipo de documento (incluido condicionalmente)",
 *                 @OA\Property(property="requiredDocumentsCount", type="integer", example=5),
 *                 @OA\Property(property="activeRequiredDocumentsCount", type="integer", example=3),
 *                 @OA\Property(property="processesCount", type="integer", example=2)
 *             ),
 *             @OA\Property(
 *                 property="meta",
 *                 type="object",
 *                 @OA\Property(property="resourceType", type="string", example="document_type"),
 *                 @OA\Property(property="generatedAt", type="string", format="date-time"),
 *                 @OA\Property(property="context", type="array", @OA\Items(type="string"))
 *             )
 *         )
 *     } 
 * )

 * @OA\Schema(
 *     schema="RequiredDocument",
 *     type="object",
 *     title="Required Document",
 *     description="Defines a document required for a process or metadata schema",
 *     @OA\Property(property="id", type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2", description="Identificador del documento requerido"),
 *     @OA\Property(property="processId", type="string", format="uuid", nullable=true, description="Proceso asociado al documento requerido"),
 *     @OA\Property(property="documentTypeId", type="string", format="uuid", description="Tipo de documento solicitado"),
 *     @OA\Property(property="academicRoleId", type="string", format="uuid", nullable=true, description="Rol académico al que aplica"),
 *     @OA\Property(property="metadataSchemaId", type="string", format="uuid", nullable=true, description="Esquema de metadatos alternativo"),
 *     @OA\Property(property="codeDefault", type="string", example="ADM-POST-REQ-2024", description="Código por defecto usado para identificar el documento"),
 *     @OA\Property(property="urlResource", type="string", nullable=true, example="https://example.com/templates/doc.pdf", description="URL del recurso de referencia"),
 *     @OA\Property(property="isPublic", type="boolean", example=false, description="Indica si el documento es visible públicamente"),
 *     @OA\Property(property="order", type="integer", example=1, description="Orden de presentación"),
 *     @OA\Property(property="mandatory", type="boolean", example=true, description="Marca si el documento es obligatorio"),
 *     @OA\Property(property="externalUserId", type="string", nullable=true, example="usr-873", description="Identificador externo del usuario que creó el registro"),
 *     @OA\Property(property="externalOrganizationId", type="string", nullable=true, example="org-221", description="Identificador externo de la organización"),
 *     @OA\Property(property="createdBy", type="string", nullable=true, example="system", description="Usuario que creó el documento"),
 *     @OA\Property(property="updatedBy", type="string", nullable=true, example="admin", description="Usuario que actualizó el documento"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2024-05-01T12:00:00Z", description="Fecha de creación"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2024-05-15T18:30:00Z", description="Fecha de última actualización")
 * )
 *
 * @OA\Schema(
 *     schema="RequiredDocumentDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/RequiredDocument"),
 *        @OA\Schema(
 *            @OA\Property(property="documentType", ref="#/components/schemas/DocumentType", description="Incluido cuando se solicita la relación"),
 *            @OA\Property(property="process", type="object", description="Proceso asociado (incluido condicionalmente)"),
 *            @OA\Property(property="metadataSchema", type="object", description="Esquema de metadatos relacionado (incluido condicionalmente)"),
 *            @OA\Property(property="academicRole", type="object", description="Rol académico relacionado (incluido condicionalmente)"),
 *            @OA\Property(property="statistics", ref="#/components/schemas/RequiredDocumentStatistics", description="Estadísticas calculadas (incluido condicionalmente)"),
 *            @OA\Property(property="meta", type="object",
 *                @OA\Property(property="resourceType", type="string", example="required_document"),
 *                @OA\Property(property="generatedAt", type="string", format="date-time"),
 *                @OA\Property(property="context", type="array", @OA\Items(type="string"))
 *            )
 *        )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="RequiredDocumentCreateRequest",
 *     type="object",
 *     title="Create Required Document Request",
 *     required={"documentTypeId"},
 *     @OA\Property(property="processId", type="string", format="uuid", nullable=true, description="Proceso al que aplica el documento (requerido si no se envía metadataSchemaId)"),
 *     @OA\Property(property="documentTypeId", type="string", format="uuid", description="Tipo de documento requerido"),
 *     @OA\Property(property="academicRoleId", type="string", format="uuid", nullable=true, description="Rol académico específico"),
 *     @OA\Property(property="metadataSchemaId", type="string", format="uuid", nullable=true, description="Esquema de metadatos alternativo (requerido si no se envía processId)"),
 *     @OA\Property(property="codeDefault", type="string", nullable=true, description="Código personalizado para el documento"),
 *     @OA\Property(property="urlResource", type="string", nullable=true, description="URL del recurso de referencia"),
 *     @OA\Property(property="isPublic", type="boolean", nullable=true, description="Visibilidad pública del documento"),
 *     @OA\Property(property="order", type="integer", minimum=0, nullable=true, description="Orden en el que se solicita el documento"),
 *     @OA\Property(property="mandatory", type="boolean", nullable=true, description="Indica si el documento es obligatorio"),
 *     @OA\Property(property="externalUserId", type="string", nullable=true, description="Identificador externo del usuario"),
 *     @OA\Property(property="externalOrganizationId", type="string", nullable=true, description="Identificador externo de la organización"),
 *     @OA\Property(property="generateDefaultCode", type="boolean", nullable=true, description="Genera automáticamente el código por defecto usando el patrón del servicio")
 * )
 *
 * @OA\Schema(
 *     schema="RequiredDocumentUpdateRequest",
 *     type="object",
 *     title="Update Required Document Request",
 *     @OA\Property(property="processId", type="string", format="uuid", nullable=true, description="Proceso asociado"),
 *     @OA\Property(property="documentTypeId", type="string", format="uuid", nullable=true, description="Tipo de documento requerido"),
 *     @OA\Property(property="academicRoleId", type="string", format="uuid", nullable=true, description="Rol académico asociado"),
 *     @OA\Property(property="metadataSchemaId", type="string", format="uuid", nullable=true, description="Esquema de metadatos alternativo"),
 *     @OA\Property(property="codeDefault", type="string", nullable=true, description="Código personalizado del documento"),
 *     @OA\Property(property="urlResource", type="string", nullable=true, description="URL del recurso de referencia"),
 *     @OA\Property(property="isPublic", type="boolean", nullable=true, description="Visibilidad pública del documento"),
 *     @OA\Property(property="order", type="integer", minimum=0, nullable=true, description="Orden en el que se solicita el documento"),
 *     @OA\Property(property="mandatory", type="boolean", nullable=true, description="Indica si el documento es obligatorio"),
 *     @OA\Property(property="externalUserId", type="string", nullable=true, description="Identificador externo de usuario"),
 *     @OA\Property(property="externalOrganizationId", type="string", nullable=true, description="Identificador externo de organización")
 * )
 *
 * @OA\Schema(
 *     schema="RequiredDocumentStatistics",
 *     type="object",
 *     title="Required Document Statistics",
 *     description="Estadísticas asociadas a un documento requerido",
 *     @OA\Property(property="total_documents_for_process", type="integer", example=5, description="Total de documentos del proceso"),
 *     @OA\Property(property="mandatory_documents_for_process", type="integer", example=3, description="Documentos obligatorios del proceso")
 * )
 *
 * @OA\Schema(
 *     schema="RequiredDocumentBulkDeleteRequest",
 *     type="object",
 *     title="Bulk Delete Required Documents Request",
 *     required={"ids"},
 *     @OA\Property(
 *         property="ids",
 *         type="array",
 *         description="Listado de IDs a eliminar",
 *         @OA\Items(type="string", format="uuid")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="StorageUnitType",
 *     type="object",
 *     title="Storage Unit Type",
 *     description="Tipo de unidad de almacenamiento",
 *     @OA\Property(property="id", type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2"),
 *     @OA\Property(property="name", type="string", example="Edificio"),
 *     @OA\Property(property="code", type="string", example="BUILDING"),
 *     @OA\Property(property="level", type="integer", example=0),
 *     @OA\Property(property="createdAt", type="string", format="date-time"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time"),
 *     @OA\Property(property="createdBy", type="string", nullable=true, example="system"),
 *     @OA\Property(property="updatedBy", type="string", nullable=true, example="system"),
 *     @OA\Property(property="version", type="integer", example=1),
 *     @OA\Property(property="storageUnitsCount", type="integer", example=4, nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="StorageUnitTypeDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/StorageUnitType"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="storageUnits",
 *                 type="array",
 *                 description="Unidades asociadas (incluidas condicionalmente)",
 *                 @OA\Items(ref="#/components/schemas/StorageUnit")
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="StorageUnitTypeCreateRequest",
 *     type="object",
 *     title="Create Storage Unit Type Request",
 *     required={"name","code","level"},
 *     @OA\Property(property="name", type="string", example="Edificio"),
 *     @OA\Property(property="code", type="string", example="BUILDING"),
 *     @OA\Property(property="level", type="integer", minimum=0, example=0)
 * )
 *
 * @OA\Schema(
 *     schema="StorageUnitTypeUpdateRequest",
 *     type="object",
 *     title="Update Storage Unit Type Request",
 *     @OA\Property(property="name", type="string", example="Edificio"),
 *     @OA\Property(property="code", type="string", example="BUILDING"),
 *     @OA\Property(property="level", type="integer", minimum=0, example=1)
 * )
 *
 * @OA\Schema(
 *     schema="StorageUnit",
 *     type="object",
 *     title="Storage Unit",
 *     description="Unidad de almacenamiento",
 *     @OA\Property(property="id", type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2"),
 *     @OA\Property(property="storageUnitTypeId", type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2"),
 *     @OA\Property(property="parentId", type="string", format="uuid", nullable=true),
 *     @OA\Property(property="label", type="string", example="Archivo Central"),
 *     @OA\Property(property="code", type="string", nullable=true, example="ARCH-001"),
 *     @OA\Property(property="createdAt", type="string", format="date-time"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time"),
 *     @OA\Property(property="createdBy", type="string", nullable=true, example="system"),
 *     @OA\Property(property="updatedBy", type="string", nullable=true, example="system"),
 *     @OA\Property(property="version", type="integer", example=1),
 *     @OA\Property(property="childrenCount", type="integer", nullable=true, example=2)
 * )
 *
 * @OA\Schema(
 *     schema="StorageUnitDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/StorageUnit"),
 *         @OA\Schema(
 *             @OA\Property(property="storageUnitType", ref="#/components/schemas/StorageUnitType", description="Tipo de unidad (incluido condicionalmente)"),
 *             @OA\Property(property="parent", ref="#/components/schemas/StorageUnit", description="Unidad padre (incluida condicionalmente)", nullable=true),
 *             @OA\Property(property="children", type="array", description="Subunidades (incluidas condicionalmente)", @OA\Items(ref="#/components/schemas/StorageUnit"))
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="StorageUnitCreateRequest",
 *     type="object",
 *     title="Create Storage Unit Request",
 *     required={"storageUnitTypeId","label"},
 *     @OA\Property(property="storageUnitTypeId", type="string", format="uuid", description="ID del tipo de unidad"),
 *     @OA\Property(property="parentId", type="string", format="uuid", nullable=true, description="ID de la unidad padre"),
 *     @OA\Property(property="label", type="string", description="Etiqueta de la unidad"),
 *     @OA\Property(property="code", type="string", nullable=true, description="Código de la unidad"),
 *     @OA\Property(property="createdBy", type="string", nullable=true, description="Usuario creador"),
 *     @OA\Property(property="updatedBy", type="string", nullable=true, description="Usuario actualizador")
 * )
 *
 * @OA\Schema(
 *     schema="StorageUnitUpdateRequest",
 *     type="object",
 *     title="Update Storage Unit Request",
 *     @OA\Property(property="storageUnitTypeId", type="string", format="uuid", description="ID del tipo de unidad"),
 *     @OA\Property(property="parentId", type="string", format="uuid", nullable=true, description="ID de la unidad padre"),
 *     @OA\Property(property="label", type="string", description="Etiqueta de la unidad"),
 *     @OA\Property(property="code", type="string", nullable=true, description="Código de la unidad"),
 *     @OA\Property(property="createdBy", type="string", nullable=true, description="Usuario creador"),
 *     @OA\Property(property="updatedBy", type="string", nullable=true, description="Usuario actualizador")
 * )
 *
 * @OA\Schema(
 *     schema="MetadataSchema",
 *     type="object",
 *     title="Metadata Schema",
 *     description="Definition of a metadata schema",
 *     @OA\Property(property="id", type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2"),
 *     @OA\Property(property="name", type="string", example="Invoice"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="parentSchemaId", type="string", format="uuid", nullable=true),
 *     @OA\Property(property="isCanonical", type="boolean", example=true),
 *     @OA\Property(property="version", type="integer", example=1),
 *     @OA\Property(property="externalSystemId", type="string", nullable=true),
 *     @OA\Property(property="apiEndpoint", type="string", nullable=true),
 *     @OA\Property(property="cacheTtl", type="integer", nullable=true, description="Cache time-to-live in seconds"),
 *     @OA\Property(property="createdAt", type="string", format="date-time"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="MetadataField",
 *     type="object",
 *     title="Metadata Field",
 *     description="Field definition within a metadata schema",
 *     @OA\Property(property="id", type="string", format="uuid", example="0197d795-7572-7331-903b-3aeed9fb34c2"),
 *     @OA\Property(property="schemaId", type="string", format="uuid", description="Parent schema ID"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="dataType", type="string", example="string"),
 *     @OA\Property(property="isRequired", type="boolean"),
 *     @OA\Property(property="defaultValue", type="string", nullable=true),
 *     @OA\Property(property="validationRegex", type="string", nullable=true),
 *     @OA\Property(property="fieldOrder", type="integer", nullable=true),
 *     @OA\Property(property="lookupKeywords", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="ocrHint", type="string", nullable=true),
 *     @OA\Property(property="ignoreInSimilarity", type="boolean"),
 *     @OA\Property(property="isReference", type="boolean"),
 *     @OA\Property(property="referenceEntity", type="string", nullable=true),
 *     @OA\Property(property="referenceColumn", type="string", nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     title="Pagination",
 *     description="Información de paginación",
 *     @OA\Property(property="currentPage", type="integer", example=1, description="Página actual"),
 *     @OA\Property(property="lastPage", type="integer", example=1, description="Última página"),
 *     @OA\Property(property="perPage", type="integer", example=15, description="Elementos por página"),
 *     @OA\Property(property="total", type="integer", example=1, description="Total de elementos"),
 *     @OA\Property(property="from", type="integer", example=1, description="Primer elemento de la página"),
 *     @OA\Property(property="to", type="integer", example=1, description="Último elemento de la página"),
 *     @OA\Property(property="hasMorePages", type="boolean", example=false, description="Si hay más páginas")
 * )
 *
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     title="Error Response",
 *     description="Respuesta de error estándar",
 *     @OA\Property(property="success", type="boolean", example=false, description="Indica si la operación fue exitosa"),
 *     @OA\Property(property="message", type="string", example="Ha ocurrido un error", description="Mensaje de error"),
 *     @OA\Property(property="errors", type="object", description="Detalles del error", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     title="Validation Error",
 *     description="Error de validación",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Los datos proporcionados no son válidos."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Errores de validación por campo",
 *         @OA\Property(
 *             property="fieldName",
 *             type="array",
 *             @OA\Items(type="string", example="El campo es requerido")
 *         )
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *     title="Success Response",
 *     description="Respuesta exitosa estándar",
 *     @OA\Property(property="success", type="boolean", example=true, description="Indica si la operación fue exitosa"),
 *     @OA\Property(property="message", type="string", example="Operación realizada exitosamente", description="Mensaje de éxito"),
 *     @OA\Property(property="data", description="Datos de respuesta", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="Department",
 *     type="object",
 *     title="Department",
 *     description="Departamento de una sede",
 *     @OA\Property(property="id", type="string", format="uuid", example="8015bc21-9a48-4b9c-a552-71d1f6f6fb15", description="ID único del departamento"),
 *     @OA\Property(property="headOfficeId", type="string", format="uuid", example="0197d888-7f98-71fc-baaa-e95ee1c28c84", description="ID de la sede a la que pertenece"),
 *     @OA\Property(property="name", type="string", example="Departamento de Informática", description="Nombre del departamento"),
 *     @OA\Property(property="code", type="string", example="INFO", description="Código único del departamento"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2025-07-04T22:36:25.000000Z", description="Fecha de creación"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2025-07-04T22:36:47.000000Z", description="Fecha de última actualización"),
 *     @OA\Property(property="createdBy", type="string", example="system", description="Usuario que creó el departamento"),
 *     @OA\Property(property="updatedBy", type="string", example="system", description="Usuario que actualizó el departamento"),
 *     @OA\Property(property="version", type="integer", example=1, description="Versión del registro"),
 *     @OA\Property(property="careersCount", type="integer", example=3, description="Número de carreras asociadas")
 * )
 *
 * @OA\Schema(
 *     schema="DepartmentDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Department"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="headOffice",
 *                 ref="#/components/schemas/HeadOffice",
 *                 description="Sede a la que pertenece (incluida condicionalmente)"
 *             ),
 *             @OA\Property(
 *                 property="careers",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Career"),
 *                 description="Carreras del departamento (incluidas condicionalmente)"
 *             ),
 *             @OA\Property(
 *                 property="statistics",
 *                 type="object",
 *                 @OA\Property(property="careersCount", type="integer", example=3),
 *                 @OA\Property(property="hasCareers", type="boolean", example=true),
 *                 @OA\Property(property="headOfficeName", type="string", example="Sede Central"),
 *                 description="Estadísticas del departamento (incluidas condicionalmente)"
 *             ),
 *             @OA\Property(
 *                 property="hierarchy",
 *                 type="object",
 *                 @OA\Property(
 *                     property="headOffice",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", format="uuid"),
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="code", type="string")
 *                 ),
 *                 @OA\Property(property="careersCount", type="integer"),
 *                 @OA\Property(
 *                     property="careers",
 *                     type="array",
 *                     @OA\Items(ref="#/components/schemas/CareerWithSubsystems")
 *                 ),
 *                 description="Jerarquía completa (incluida condicionalmente)"
 *             ),
 *             @OA\Property(
 *                 property="meta",
 *                 type="object",
 *                 @OA\Property(property="resourceType", type="string", example="department"),
 *                 @OA\Property(property="generatedAt", type="string", format="date-time"),
 *                 @OA\Property(property="context", type="array", @OA\Items(type="string"))
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="Career",
 *     type="object",
 *     title="Career",
 *     description="Carrera académica",
 *     @OA\Property(property="id", type="string", format="uuid", example="310331bc-9201-472f-8d23-26ebcd3a8fdf", description="ID único de la carrera"),
 *     @OA\Property(property="departmentId", type="string", format="uuid", example="8015bc21-9a48-4b9c-a552-71d1f6f6fb15", description="ID del departamento al que pertenece"),
 *     @OA\Property(property="name", type="string", example="Ingeniería de Sistemas", description="Nombre de la carrera"),
 *     @OA\Property(property="code", type="string", example="ISIST", description="Código de la carrera"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", description="Fecha de última actualización"),
 *     @OA\Property(property="createdBy", type="string", example="system", description="Usuario que creó la carrera"),
 *     @OA\Property(property="updatedBy", type="string", example="system", description="Usuario que actualizó la carrera"),
 *     @OA\Property(property="version", type="integer", example=1, description="Versión del registro")
 * )
 *
 * @OA\Schema(
 *     schema="CareerWithSubsystems",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Career"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="subsystems",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Subsystem"),
 *                 description="Subsistemas asociados a la carrera"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="Subsystem",
 *     type="object",
 *     title="Subsystem",
 *     description="Subsistema del sistema de gestión",
 *     @OA\Property(property="id", type="string", format="uuid", example="41091aec-4181-4546-b5dd-f97b34876015", description="ID único del subsistema"),
 *     @OA\Property(property="name", type="string", example="Subsistema de Prueba", description="Nombre del subsistema"),
 *     @OA\Property(property="code", type="string", example="SPRUEBA", description="Código único del subsistema"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", description="Fecha de última actualización"),
 *     @OA\Property(property="createdBy", type="string", example="system", description="Usuario que creó el subsistema"),
 *     @OA\Property(property="updatedBy", type="string", example="system", description="Usuario que actualizó el subsistema"),
 *     @OA\Property(property="version", type="integer", example=1, description="Versión del registro")
 * )
 *
 * @OA\Schema(
 *     schema="DropdownOption",
 *     type="object",
 *     title="Dropdown Option",
 *     description="Opción para dropdown/select",
 *     @OA\Property(property="value", type="string", format="uuid", example="8015bc21-9a48-4b9c-a552-71d1f6f6fb15", description="Valor de la opción"),
 *     @OA\Property(property="label", type="string", example="Departamento de Informática", description="Etiqueta mostrada"),
 *     @OA\Property(property="code", type="string", example="INFO", description="Código adicional (opcional)")
 * )
 *
 * @OA\Schema(
 *     schema="PluckOption",
 *     type="object",
 *     title="Pluck Option",
 *     description="Opción para formato pluck (key-value)",
 *     @OA\Property(property="value", description="Valor de la clave", example="8015bc21-9a48-4b9c-a552-71d1f6f6fb15"),
 *     @OA\Property(property="label", type="string", example="Departamento de Informática", description="Etiqueta del valor")
 * )
 *
 * @OA\Schema(
 *     schema="BulkDeleteResponse",
 *     type="object",
 *     title="Bulk Delete Response",
 *     description="Respuesta de operación de eliminación masiva",
 *     @OA\Property(property="deletedCount", type="integer", example=2, description="Número de elementos eliminados exitosamente"),
 *     @OA\Property(property="totalRequested", type="integer", example=3, description="Número total de elementos solicitados para eliminación"),
 *     @OA\Property(property="successRate", type="string", example="66.67%", description="Porcentaje de éxito de la operación")
 * )
 *
 * @OA\Schema(
 *     schema="ProcessCategory",
 *     type="object",
 *     title="Process Category",
 *     description="Entidad de categoría de proceso",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="uuid",
 *         description="Identificador único de la categoría de proceso",
 *         example="550e8400-e29b-41d4-a716-446655440000"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nombre de la categoría de proceso",
 *         example="Procesos de Admisiones",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         description="Código único de la categoría de proceso",
 *         example="ADMISIONES",
 *         maxLength=255,
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="subsystemId",
 *         type="string",
 *         format="uuid",
 *         description="ID del subsistema al que pertenece la categoría",
 *         example="550e8400-e29b-41d4-a716-446655440001"
 *     ),
 *     @OA\Property(
 *         property="createdAt",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora de creación",
 *         example="2025-08-02T10:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updatedAt",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora de última actualización",
 *         example="2025-08-02T12:30:00Z"
 *     ),
 *     @OA\Property(
 *         property="processes",
 *         type="array",
 *         description="Lista de procesos asociados a esta categoría",
 *         @OA\Items(ref="#/components/schemas/Process"),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="subsystem",
 *         ref="#/components/schemas/Subsystem",
 *         description="Subsistema al que pertenece la categoría",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="processesCount",
 *         type="integer",
 *         description="Número de procesos asociados",
 *         example=3
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Process",
 *     type="object",
 *     title="Process",
 *     description="Entidad de proceso",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="uuid",
 *         description="Identificador único del proceso",
 *         example="550e8400-e29b-41d4-a716-446655440002"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nombre del proceso",
 *         example="Inscripción de Nuevos Estudiantes"
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         description="Código del proceso",
 *         example="INSCRIPCION",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="order",
 *         type="integer",
 *         description="Orden del proceso dentro de la categoría",
 *         example=1
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="StoreProcessCategoryRequest",
 *     type="object",
 *     title="Store Process Category Request",
 *     description="Datos requeridos para crear una nueva categoría de proceso",
 *     required={"name", "code", "subsystem_id"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nombre de la categoría de proceso",
 *         example="Procesos de Admisiones",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         description="Código único de la categoría (solo letras mayúsculas, números, guiones y guiones bajos)",
 *         example="ADMISIONES",
 *         maxLength=255,
 *         pattern="^[A-Z0-9_-]+$"
 *     ),
 *     @OA\Property(
 *         property="subsystem_id",
 *         type="string",
 *         format="uuid",
 *         description="ID del subsistema al que pertenecerá la categoría",
 *         example="550e8400-e29b-41d4-a716-446655440001"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UpdateProcessCategoryRequest",
 *     type="object",
 *     title="Update Process Category Request",
 *     description="Datos para actualizar una categoría de proceso existente",
 *     required={"name", "code"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nombre actualizado de la categoría de proceso",
 *         example="Procesos de Admisiones Actualizados",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         description="Código único actualizado de la categoría",
 *         example="ADMISIONES_V2",
 *         maxLength=255,
 *         pattern="^[A-Z0-9_-]+$"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ProcessCategoryDropdownResponse",
 *     type="object",
 *     title="Process Category Dropdown Response",
 *     description="Respuesta formateada para dropdowns de categorías de proceso",
 *     @OA\Property(
 *         property="options",
 *         type="array",
 *         description="Lista de opciones para el dropdown",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(
 *                 property="value",
 *                 type="string",
 *                 format="uuid",
 *                 description="ID de la categoría",
 *                 example="550e8400-e29b-41d4-a716-446655440000"
 *             ),
 *             @OA\Property(
 *                 property="label",
 *                 type="string",
 *                 description="Nombre de la categoría para mostrar",
 *                 example="Procesos de Admisiones"
 *             ),
 *             @OA\Property(
 *                 property="code",
 *                 type="string",
 *                 description="Código de la categoría",
 *                 example="ADMISIONES"
 *             )
 *         )
 *     ),
 *     @OA\Property(
 *         property="count",
 *         type="integer",
 *         description="Número total de opciones disponibles",
 *         example=15
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     title="Validation Error Response",
 *     description="Respuesta estándar para errores de validación",
 *     @OA\Property(
 *         property="success",
 *         type="boolean",
 *         description="Indica si la operación fue exitosa",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Mensaje general del error",
 *         example="Los datos proporcionados no son válidos"
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Detalles específicos de errores por campo",
 *         example={
 *             "name": {"El campo nombre es obligatorio"},
 *             "code": {"El código debe contener solo letras mayúsculas, números, guiones y guiones bajos"}
 *         }
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="NotFoundResponse",
 *     type="object",
 *     title="Not Found Response",
 *     description="Respuesta estándar para recursos no encontrados",
 *     @OA\Property(
 *         property="success",
 *         type="boolean",
 *         description="Indica si la operación fue exitosa",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Mensaje del error",
 *         example="La categoría de proceso no fue encontrada"
 *     ),
 *     @OA\Property(
 *         property="error_code",
 *         type="string",
 *         description="Código específico del error",
 *         example="RESOURCE_NOT_FOUND"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="Error Response",
 *     description="Respuesta estándar para errores del servidor",
 *     @OA\Property(
 *         property="success",
 *         type="boolean",
 *         description="Indica si la operación fue exitosa",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Mensaje del error",
 *         example="Ha ocurrido un error interno del servidor"
 *     ),
 *     @OA\Property(
 *         property="error_code",
 *         type="string",
 *         description="Código específico del error",
 *         example="INTERNAL_SERVER_ERROR"
 *     ),
 *     @OA\Property(
 *         property="trace_id",
 *         type="string",
 *         description="ID de trazabilidad para debugging",
 *         example="abc123def456",
 *         nullable=true
 *     )
 * )
 */
class SwaggerSchemas
{
    // Esta clase solo existe para contener los esquemas de Swagger
}
