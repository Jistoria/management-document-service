<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaginateResource extends JsonResource
{

    public function __construct(
    public $resource,
    protected string|null $resourceName = null)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'currentPage' => $this->resource->currentPage(),
            'lastPage' => $this->resource->lastPage(),
            'perPage' => $this->resource->perPage(),
            'total' => $this->resource->total(),
            'data' => $this->resourceName ? $this->resourceName::collection($this->resource->items()) : $this->resource->items(),
        ];
    }
}
