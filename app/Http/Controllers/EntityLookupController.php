<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use function App\Helpers\catchSync;

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