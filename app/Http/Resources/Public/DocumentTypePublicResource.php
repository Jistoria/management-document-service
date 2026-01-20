<?php

namespace App\Http\Resources\Public;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * Public-safe resource for Document Type
 * 
 * Expone únicamente datos seguros sin información sensible
 */
class DocumentTypePublicResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    public function minimal(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
        ];
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
        ];
    }

    protected function getResourceType(): string
    {
        return 'publicDocumentType';
    }

    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(fn($item) => [
                'value' => $item->id,
                'label' => $item->name,
                'code' => $item->code,
            ])->values(),
            'count' => $collection->count()
        ];
    }

    public function with(Request $request): array
    {
        return [];
    }
}
