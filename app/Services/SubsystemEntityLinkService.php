<?php

namespace App\Services;

use App\Models\Subsystem;
use App\Models\SubsystemGroup;
use App\Models\HeadOffice;
use App\Models\Department;
use App\Models\Career;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SubsystemEntityLinkService
{
    private array $allowedEntityTypes = [
        'head_office' => HeadOffice::class,
        'department' => Department::class,
        'career' => Career::class,
    ];

    public function getLinksForEntity(string $entityType, string $entityId): Collection
    {
        $this->validateEntityType($entityType);

        $entity = $this->findEntity($entityType, $entityId);

        $entity->load('subsystems');

        return $entity->subsystems()
            ->select('subsystems.id', 'subsystems.name', 'subsystems.code')
            ->get();
    }


    public function attachSubsystemToEntity(string $subsystemId, string $entityType, string $entityId): bool
    {
        $this->validateEntityType($entityType);

        $subsystem = Subsystem::findOrFail($subsystemId);
        $entity = $this->findEntity($entityType, $entityId);

        try {
            DB::beginTransaction();

            // Check if relationship already exists
            $exists = DB::table('subsystem_entity_links')
                ->where('subsystem_id', $subsystemId)
                ->where('entity_type', $this->allowedEntityTypes[$entityType])
                ->where('entity_id', $entityId)
                ->exists();

            if ($exists) {
                DB::rollBack();
                return false; // Already exists
            }

            // Create a relationship
            DB::table('subsystem_entity_links')->insert([
                'id' => Str::uuid(),
                'subsystem_id' => $subsystemId,
                'entity_type' => $this->allowedEntityTypes[$entityType],
                'entity_id' => $entityId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function syncSubsystemToEntity(string $subsystemId, array $entities): bool
    {
        $subsystem = Subsystem::findOrFail($subsystemId);

        return DB::transaction(function () use ($subsystem, $entities) {
            foreach ($entities as $entity) {
                $type = $entity['entity_type'];
                $ids = $entity['entity_ids'];

                switch ($type) {
                    case 'head_office':
                        $subsystem->headOffices()->syncWithoutDetaching($ids);
                        break;

                    case 'department':
                        $subsystem->departments()->syncWithoutDetaching($ids);
                        break;

                    case 'career':
                        $subsystem->careers()->syncWithoutDetaching($ids);
                        break;

                    default:
                        throw new \InvalidArgumentException("Tipo de entidad no soportado: $type");
                }
            }

            return true;
        });
    }


    public function detachSubsystemFromEntity(string $subsystemId, string $entityType, string $entityId): bool
    {
        $this->validateEntityType($entityType);

        $affected = DB::table('subsystem_entity_links')
            ->where('subsystem_id', $subsystemId)
            ->where('entity_type', $this->allowedEntityTypes[$entityType])
            ->where('entity_id', $entityId)
            ->delete();

        return $affected > 0;
    }

    public function getSubsystemEntities(string $subsystemId): array
    {
        $subsystem = Subsystem::findOrFail($subsystemId);

        $links = DB::table('subsystem_entity_links')
            ->where('subsystem_id', $subsystemId)
            ->get();

        $result = [];

        foreach ($links as $link) {
            $entityType = $this->getEntityTypeKey($link->entity_type);
            $entity = $this->findEntityByClass($link->entity_type, $link->entity_id);

            if ($entity) {
                $result[] = [
                    'entity_type' => $entityType,
                    'entity_id' => $link->entity_id,
                    'entity_data' => [
                        'id' => $entity->id,
                        'name' => $entity->name,
                        'code' => $entity->code ?? null,
                    ]
                ];
            }
        }

        return $result;
    }

    private function validateEntityType(string $entityType): void
    {
        if (!array_key_exists($entityType, $this->allowedEntityTypes)) {
            throw new InvalidArgumentException("Invalid entity type: {$entityType}");
        }
    }

    private function findEntity(string $entityType, string $entityId): Model
    {
        $modelClass = $this->allowedEntityTypes[$entityType];
        return $modelClass::findOrFail($entityId);
    }

    private function findEntityByClass(string $entityClass, string $entityId): ?Model
    {
        try {
            return $entityClass::find($entityId);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getEntityTypeKey(string $entityClass): string
    {
        $mapping = array_flip($this->allowedEntityTypes);
        return $mapping[$entityClass] ?? 'unknown';
    }
}
