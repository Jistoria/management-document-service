<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Resource for paginated responses
 *
 * Provides consistent pagination format across all resources
 */
class PaginateResource extends JsonResource
{
    public function __construct(
        public $resource,
        protected string|null $resourceClass = null
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (!$this->resource instanceof LengthAwarePaginator) {
            throw new \InvalidArgumentException('Resource must be a LengthAwarePaginator instance');
        }

        return [
            'data' => $this->resourceClass
                ? $this->resourceClass::collection($this->resource->items())
                : $this->resource->items(),
            'pagination' => [
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
                'from' => $this->resource->firstItem(),
                'to' => $this->resource->lastItem(),
                'has_more_pages' => $this->resource->hasMorePages(),
                'path' => $this->resource->path(),
                'links' => [
                    'first' => $this->resource->url(1),
                    'last' => $this->resource->url($this->resource->lastPage()),
                    'prev' => $this->resource->previousPageUrl(),
                    'next' => $this->resource->nextPageUrl(),
                ],
            ],
            'meta' => [
                'resource_type' => 'paginated',
                'generated_at' => now()->toISOString(),
            ]
        ];
    }

    /**
     * Create a paginated response with specific resource class
     */
    public static function paginate(LengthAwarePaginator $paginator, string $resourceClass = null): self
    {
        return new self($paginator, $resourceClass);
    }
}
