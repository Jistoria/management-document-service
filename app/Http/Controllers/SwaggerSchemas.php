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
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-04T22:36:25.000000Z", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-04T22:36:47.000000Z", description="Fecha de última actualización"),
 *     @OA\Property(property="created_by", type="string", example="system", description="Usuario que creó la sede"),
 *     @OA\Property(property="updated_by", type="string", example="system", description="Usuario que actualizó la sede"),
 *     @OA\Property(property="version", type="integer", example=2, description="Versión del registro"),
 *     @OA\Property(property="departments_count", type="integer", example=0, description="Número de departamentos asociados")
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
 *                 @OA\Property(property="resource_type", type="string", example="head_office"),
 *                 @OA\Property(property="generated_at", type="string", format="date-time"),
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
 *     schema="Pagination",
 *     type="object",
 *     title="Pagination",
 *     description="Información de paginación",
 *     @OA\Property(property="current_page", type="integer", example=1, description="Página actual"),
 *     @OA\Property(property="last_page", type="integer", example=1, description="Última página"),
 *     @OA\Property(property="per_page", type="integer", example=15, description="Elementos por página"),
 *     @OA\Property(property="total", type="integer", example=1, description="Total de elementos"),
 *     @OA\Property(property="from", type="integer", example=1, description="Primer elemento de la página"),
 *     @OA\Property(property="to", type="integer", example=1, description="Último elemento de la página"),
 *     @OA\Property(property="has_more_pages", type="boolean", example=false, description="Si hay más páginas")
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
 *             property="field_name",
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
 *     @OA\Property(property="head_office_id", type="string", format="uuid", example="0197d888-7f98-71fc-baaa-e95ee1c28c84", description="ID de la sede a la que pertenece"),
 *     @OA\Property(property="name", type="string", example="Departamento de Informática", description="Nombre del departamento"),
 *     @OA\Property(property="code", type="string", example="INFO", description="Código único del departamento"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-04T22:36:25.000000Z", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-04T22:36:47.000000Z", description="Fecha de última actualización"),
 *     @OA\Property(property="created_by", type="string", example="system", description="Usuario que creó el departamento"),
 *     @OA\Property(property="updated_by", type="string", example="system", description="Usuario que actualizó el departamento"),
 *     @OA\Property(property="version", type="integer", example=1, description="Versión del registro"),
 *     @OA\Property(property="careers_count", type="integer", example=3, description="Número de carreras asociadas")
 * )
 *
 * @OA\Schema(
 *     schema="DepartmentDetailed",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Department"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="head_office",
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
 *                 @OA\Property(property="careers_count", type="integer", example=3),
 *                 @OA\Property(property="has_careers", type="boolean", example=true),
 *                 @OA\Property(property="head_office_name", type="string", example="Sede Central"),
 *                 description="Estadísticas del departamento (incluidas condicionalmente)"
 *             ),
 *             @OA\Property(
 *                 property="hierarchy",
 *                 type="object",
 *                 @OA\Property(
 *                     property="head_office",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", format="uuid"),
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="code", type="string")
 *                 ),
 *                 @OA\Property(property="careers_count", type="integer"),
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
 *                 @OA\Property(property="resource_type", type="string", example="department"),
 *                 @OA\Property(property="generated_at", type="string", format="date-time"),
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
 *     @OA\Property(property="department_id", type="string", format="uuid", example="8015bc21-9a48-4b9c-a552-71d1f6f6fb15", description="ID del departamento al que pertenece"),
 *     @OA\Property(property="name", type="string", example="Ingeniería de Sistemas", description="Nombre de la carrera"),
 *     @OA\Property(property="code", type="string", example="ISIST", description="Código de la carrera"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de última actualización"),
 *     @OA\Property(property="created_by", type="string", example="system", description="Usuario que creó la carrera"),
 *     @OA\Property(property="updated_by", type="string", example="system", description="Usuario que actualizó la carrera"),
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
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de última actualización"),
 *     @OA\Property(property="created_by", type="string", example="system", description="Usuario que creó el subsistema"),
 *     @OA\Property(property="updated_by", type="string", example="system", description="Usuario que actualizó el subsistema"),
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
 *     @OA\Property(property="deleted_count", type="integer", example=2, description="Número de elementos eliminados exitosamente"),
 *     @OA\Property(property="total_requested", type="integer", example=3, description="Número total de elementos solicitados para eliminación"),
 *     @OA\Property(property="success_rate", type="string", example="66.67%", description="Porcentaje de éxito de la operación")
 * )
 */
class SwaggerSchemas
{
    // Esta clase solo existe para contener los esquemas de Swagger
}
