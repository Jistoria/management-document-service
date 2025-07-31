<?php

namespace App\Http\Resources;

use App\Models\Career;
use Illuminate\Http\Request;

class SubsystemResource extends BaseResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'createdBy' => $this->created_by,
            'updatedBy' => $this->updated_by,
            'version' => $this->version,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'countCareers' => $this->careers->count(),
            'countHeadOffices' => $this->headOffices->count(),
            'countDepartments' => $this->departments->count()
        ];
    }

    protected function getMinimalFields(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
        ];
    }

    protected function getResourceType(): string
    {
        return 'subsystem';
    }

    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(function ($subsystem) {
                return [
                    'value' => $subsystem->id,
                    'label' => $subsystem->name,
                    'code' => $subsystem->code,
                ];
            }),
            'count' => count($collection)
        ];
    }

    public function withHierarchy(): array
    {
        return array_merge($this->toArray(request()), [
            'processCategories' => $this->whenLoaded('processCategories', function () {
                return ProcessCategoryResource::collection($this->processCategories);
            }),
        ]);
    }
}
