<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use function App\Helpers\catchSync;

/**
 * @OA\Post(
 *     path="/api/me/entities",
 *     tags={"Auth Integration"},
 *     summary="Traduce team_ids de la sesión a entidades completas",
 *     description="Endpoint de integración con auth-service. Recibe los team_ids de la sesión del usuario y retorna las entidades completas (Facultades/Carreras) bajo las cuales el usuario puede actuar. Los team_ids vienen en formato FAC:123 o CARR:456.",
 *     security={{"passport": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Los team_ids vienen automáticamente del middleware auth.service en $request->get('session')['team_ids']",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="session",
 *                 type="object",
 *                 description="Objeto de sesión inyectado por el middleware auth.service",
 *                 @OA\Property(
 *                     property="team_ids",
 *                     type="array",
 *                     description="Array de identificadores de equipos/entidades con prefijo FAC: o CARR:",
 *                     example={"FAC:101", "FAC:102", "CARR:201", "CARR:202"},
 *                     @OA\Items(type="string")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Entidades traducidas exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Operación exitosa"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 description="Colección de entidades traducidas",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="string", format="uuid", example="9d5e8b3a-1c2d-4e5f-8a9b-0c1d2e3f4a5b", description="UUID de la entidad"),
 *                     @OA\Property(property="teamId", type="string", example="FAC:101", description="Identificador original del team con prefijo"),
 *                     @OA\Property(property="code", type="string", example="FIN", description="Código alfabético de la entidad"),
 *                     @OA\Property(property="codeNumeric", type="string", example="101", description="Código numérico de la entidad"),
 *                     @OA\Property(property="name", type="string", example="Facultad de Ingeniería", description="Nombre completo de la entidad"),
 *                     @OA\Property(property="type", type="string", enum={"Facultad", "Carrera"}, example="Facultad", description="Tipo de entidad"),
 *                     @OA\Property(property="entity", type="string", enum={"Area", "Carrera"}, example="Area", description="Clasificación de la entidad para el sistema")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="No autenticado o token inválido",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Unauthenticated")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Error al procesar las entidades")
 *         )
 *     )
 * )
 */
class EntityLookupController extends Controller
{
    public function __invoke(Request $request)
    {

        return catchSync(function () use ($request) {

            $codes = $request->get('session')['team_ids'];

            $results = collect();

            $facCodes = collect($codes)->filter(fn($c) => str_starts_with($c, 'FAC:'))
                ->map(fn($c) => str_replace('FAC:', '', $c));
            $carrCodes = collect($codes)->filter(fn($c) => str_starts_with($c, 'CARR:'))
                ->map(fn($c) => str_replace('CARR:', '', $c));

            if ($facCodes->isNotEmpty()) {
                $facultades = \App\Models\Department::whereIn('code_numeric', $facCodes)->get(['code', 'name', 'id', 'code_numeric']);
                
                $results = $results->merge($facultades->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'teamId' => 'FAC:' . $item->code_numeric,
                        'code' => $item->code,
                        'codeNumeric' => $item->code_numeric,
                        'name' => $item->name,
                        'type' => 'Facultad',
                        'entity' => 'Area'
                    ];
                }));
            }

            if ($carrCodes->isNotEmpty()) {
                $carreras = \App\Models\Career::whereIn('code_numeric', $carrCodes)->get(['code', 'name', 'id', 'code_numeric']);
                
                $results = $results->merge($carreras->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'teamId' => 'CARR:' . $item->code_numeric,
                        'code' => $item->code,
                        'codeNumeric' => $item->code_numeric,
                        'name' => $item->name,
                        'type' => 'Carrera',
                        'entity' => 'Carrera'
                    ];
                }));
            }

            return $results;
        });
    }
}