<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Management Document Service API",
 *     version="1.0.0",
 *     description="API para el servicio de gestión de documentos. Microservicio para manejo de sedes, departamentos, carreras y documentos.",
 *     @OA\Contact(
 *         email="admin@management-service.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000/api",
 *     description="Local Development Server"
 * )
 *
 * @OA\Tag(
 *     name="HeadOffices",
 *     description="Operaciones para gestión de Sedes"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    //
}
