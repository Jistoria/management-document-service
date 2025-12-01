<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use function App\Helpers\catchSync;

class EntityLookupController extends Controller
{
    /**
     * @OA\Post(
     *     path="/me/entities",
     *     operationId="getMyEntities",
     *     tags={"Auth Integration"},
     *     summary="Traduce team_ids de la sesión a entidades completas",
     *     description="Endpoint de integración con auth-service. Recibe los team_ids de la sesión del usuario (inyectados por middleware auth.service) y retorna las entidades completas (Facultades/Carreras) bajo las cuales el usuario puede actuar. Los team_ids vienen en formato FAC:123 o CARR:456.",
     *     @OA\RequestBody(
     *         description="Los team_ids vienen automáticamente del middleware auth.service en la sesión del request. No se envían en el body explícitamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             example={}
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
     *                 description="Colección de entidades traducidas desde los team_ids de la sesión",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="9d5e8b3a-1c2d-4e5f-8a9b-0c1d2e3f4a5b", description="UUID de la entidad"),
     *                     @OA\Property(property="teamId", type="string", example="FAC:101", description="Identificador original del team con prefijo (FAC: o CARR:)"),
     *                     @OA\Property(property="code", type="string", example="FIN", description="Código alfabético de la entidad"),
     *                     @OA\Property(property="codeNumeric", type="string", example="101", description="Código numérico usado en team_ids"),
     *                     @OA\Property(property="name", type="string", example="Facultad de Ingeniería", description="Nombre completo de la entidad"),
     *                     @OA\Property(property="type", type="string", enum={"Facultad", "Carrera"}, example="Facultad", description="Tipo de entidad"),
     *                     @OA\Property(property="entity", type="string", enum={"Area", "Carrera"}, example="Area", description="Clasificación de la entidad (Area para Facultades, Carrera para Carreras)")
     *                 ),
     *                 example={
     *                     {
     *                         "id": "9d5e8b3a-1c2d-4e5f-8a9b-0c1d2e3f4a5b",
     *                         "teamId": "FAC:101",
     *                         "code": "FIN",
     *                         "codeNumeric": "101",
     *                         "name": "Facultad de Ingeniería",
     *                         "type": "Facultad",
     *                         "entity": "Area"
     *                     },
     *                     {
     *                         "id": "9d5e8b3a-1c2d-4e5f-8a9b-0c1d2e3f4a5c",
     *                         "teamId": "CARR:201",
     *                         "code": "INGSIST",
     *                         "codeNumeric": "201",
     *                         "name": "Ingeniería de Sistemas",
     *                         "type": "Carrera",
     *                         "entity": "Carrera"
     *                     }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado o token inválido",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
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