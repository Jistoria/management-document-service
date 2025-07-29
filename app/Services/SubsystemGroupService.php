<?php

namespace App\Services;

use App\Constants\HttpStatus;
use App\Models\Subsystem;
use App\Models\SubsystemGroup;

class SubsystemGroupService
{
    public function __construct(
        protected SubsystemGroup $subsystemGroup
    )
    {}

    public function getAll(array $filters)
    {
        return $this->subsystemGroup->all();
    }

    public function syncSubsystems(string $id, array $subsystemIds): void
    {
        if(empty($subsystemIds)) throw new \Exception("No se han seleccionado subsystemas", HttpStatus::BAD_REQUEST);
        $this->subsystemGroup->findOrFail($id)->subsystems()->sync($subsystemIds);
    }

}
