<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacultyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    public function toArrayForPagination(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code_document' => $this->code_document,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
