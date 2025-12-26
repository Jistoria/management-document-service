<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MetadataSchema;
use App\Models\HeadOffice;
use App\Models\User;
use App\Http\Resources\MetadataSchemaResource;

class SyncExportController extends Controller
{
    public function export()
    {
        // Carga ansiosa (Eager Loading) para eficiencia
        return [
            'schemas' => MetadataSchemaResource::collection(MetadataSchema::with('metadataFields')->get()),
            'structure' => HeadOffice::with(['departments.careers'])->get(),
        ];
    }
}
