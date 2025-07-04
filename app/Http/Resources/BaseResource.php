<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base Resource class with flexible transformation methods
 *
 * Provides methods for different use cases: pagination, collections, pluck, show, etc.
 */
abstract class BaseResource extends JsonResource
{
    /**
     * Context for the resource transformation
     */
    protected array $context = [];

    /**
     * Set transformation context
     */
    public function withContext(array $context): static
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Transform for paginated responses
     */
    public static function paginated($paginator): array
    {
        if (!$paginator instanceof LengthAwarePaginator) {
            throw new \InvalidArgumentException('Expected LengthAwarePaginator instance');
        }

        return [
            'data' => static::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ]
        ];
    }

    /**
     * Transform for simple collections
     */
    public static function simpleCollection($collection): array
    {
        return [
            'data' => static::collection($collection),
            'count' => count($collection)
        ];
    }

    /**
     * Transform for pluck operations (key-value pairs)
     */
    public static function pluck($collection, string $valueKey, string $labelKey = 'name'): array
    {
        return [
            'data' => $collection->map(function ($item) use ($valueKey, $labelKey) {
                return [
                    'value' => $item->{$valueKey},
                    'label' => $item->{$labelKey},
                ];
            }),
            'count' => count($collection)
        ];
    }

    /**
     * Transform for dropdown/select options
     */
    public static function dropdown($collection, string $valueKey = 'id', string $labelKey = 'name'): array
    {
        return [
            'options' => $collection->map(function ($item) use ($valueKey, $labelKey) {
                return [
                    'value' => $item->{$valueKey},
                    'label' => $item->{$labelKey},
                ];
            }),
            'count' => count($collection)
        ];
    }

    /**
     * Transform for detailed view (show)
     */
    public function detailed(): array
    {
        return array_merge($this->toArray(request()), [
            'meta' => $this->getDetailedMeta(),
        ]);
    }

    /**
     * Transform for minimal view (listing)
     */
    public function minimal(): array
    {
        return $this->getMinimalFields();
    }

    /**
     * Get metadata for detailed views
     */
    protected function getDetailedMeta(): array
    {
        return [
            'resource_type' => $this->getResourceType(),
            'generated_at' => now()->toISOString(),
            'context' => $this->context,
        ];
    }

    /**
     * Get minimal fields for listing views
     */
    abstract protected function getMinimalFields(): array;

    /**
     * Get resource type identifier
     */
    abstract protected function getResourceType(): string;

    /**
     * Check if should include relationships
     */
    protected function shouldIncludeRelations(Request $request): bool
    {
        return $request->has('include') || !empty($this->context['include_relations']);
    }

    /**
     * Get requested includes from query parameter
     */
    protected function getRequestedIncludes(Request $request): array
    {
        $includes = $request->get('include', '');
        return $includes ? explode(',', $includes) : [];
    }

    /**
     * Check if specific relation should be included
     */
    protected function shouldInclude(string $relation, Request $request): bool
    {
        $requested = $this->getRequestedIncludes($request);
        return in_array($relation, $requested) ||
            in_array('*', $requested) ||
            !empty($this->context['include_relations']) &&
            in_array($relation, $this->context['include_relations']);
    }
}
