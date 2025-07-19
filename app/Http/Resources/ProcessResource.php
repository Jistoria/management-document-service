<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ProcessResource extends BaseResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
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
        return 'process';
    }

    public static function forDropdown($collection): array
    {
        return [
            'options' => $collection->map(function ($process) {
                return [
                    'value' => $process->id,
                    'label' => $process->name,
                    'code' => $process->code,
                ];
            }),
            'count' => count($collection)
        ];
    }
}
