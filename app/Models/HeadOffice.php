<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use App\Traits\Auditable; // 👈 Agregar trait de auditoría
use App\Traits\HasCamelCaseAttributes; // 👈 Agregar trait para camelCase
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for Head Offices (Sedes)
 *
 * Represents the main headquarters or campuses of an organization.
 * This is the top level of the organizational hierarchy.
 *
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string|null $code_numeric
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property int $version
 */
class HeadOffice extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasAuditFields, Auditable, HasCamelCaseAttributes {
        Auditable::getCurrentExternalUserId insteadof HasAuditFields;
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'head_offices';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'code_numeric',
        'created_by',
        'updated_by',
        'version'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'version' => 'integer'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Get the departments for this head office.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }


    /**
     * Get the subsystems associated with this head office.
     */
    public function subsystems(): MorphToMany
    {
        return $this->morphToMany(
            Subsystem::class,
            'entity',
            'subsystem_entity_links',
            'entity_id',
            'subsystem_id'
        )->withTimestamps();
    }

    /**
     * Get active departments for this head office.
     */
    public function activeDepartments(): HasMany
    {
        return $this->departments()->whereNull('deleted_at');
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope to get active head offices.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope to get head offices with or without subsystems.
     */
    public function scopeHasSubsystems($query, $value = true)
    {
        return $value ? $query->has('subsystems') : $query->doesntHave('subsystems');
    }

    /**
     * Scope to get head offices with id subsystem.
     */
    public function scopeWithSubsystemId($query, string $subsystemId)
    {
        return $query->whereHas('subsystems', function ($q) use ($subsystemId) {
            $q->where('subsystems.id', $subsystemId);
        });
    }

    /**
     * Scope to get head offices without a specific subsystem.
     */
    public function scopeWithoutSubsystemId($query, string $subsystemId)
    {
        return $query->whereDoesntHave('subsystems', function ($q) use ($subsystemId) {
            $q->where('subsystems.id', $subsystemId);
        });
    }

    /**
     * Get full organizational hierarchy starting from this head office.
     */
    public function getFullHierarchy()
    {
        return $this->load([
            'departments.careers.subsystems',
            'departments.careers.subsystems.processCategories.processes.requiredDocuments',
        ]);
    }
}
