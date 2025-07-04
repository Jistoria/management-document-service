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
 */
class SwaggerSchemas
{
    // Esta clase solo existe para contener los esquemas de Swagger
}
