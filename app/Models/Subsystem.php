<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use App\Traits\Auditable; // 👈 Agregar trait de auditoría
use App\Traits\HasCamelCaseAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for Subsystems
 *
 * Represents subsystems within the document management system.
 * Subsystems can be associated with multiple careers and contain process categories.
 *
 * @property string $id
 * @property string $name
 * @property string $code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property int $version
 */
class Subsystem extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasAuditFields, Auditable, HasCamelCaseAttributes {
        Auditable::getCurrentExternalUserId insteadof HasAuditFields;
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'subsystems';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
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
     * Get the head offices associated with this subsystem.
     */
    public function headOffices(): MorphToMany
    {
        return $this->morphedByMany(
            HeadOffice::class,
            'entity',
            'subsystem_entity_links',
            'subsystem_id',
            'entity_id'
        )->withTimestamps();
    }

    /**
     * Get the departments associated with this subsystem.
     */
    public function departments(): MorphToMany
    {
        return $this->morphedByMany(
            Department::class,
            'entity',
            'subsystem_entity_links',
            'subsystem_id',
            'entity_id'
        )->withTimestamps();
    }

    /**
     * Get the careers associated with this subsystem via entity links.
     */
    public function careers(): MorphToMany
    {
        return $this->morphedByMany(
            Career::class,
            'entity',
            'subsystem_entity_links',
            'subsystem_id',
            'entity_id'
        )->withTimestamps();
    }

    /**
     * Get the groups associated with this subsystem.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(SubsystemGroup::class, 'subsystem_group_links', 'subsystem_id', 'group_id')
            ->withTimestamps();
    }

    /**
     * Get active careers associated with this subsystem.
     */
    public function activeCareers(): BelongsToMany
    {
        return $this->careers()->whereNull('careers.deleted_at');
    }

    /**
     * Get the process categories for this subsystem.
     */
    public function processCategories(): HasMany
    {
        return $this->hasMany(ProcessCategory::class);
    }

    /**
     * Get active process categories for this subsystem.
     */
    public function activeProcessCategories(): HasMany
    {
        return $this->processCategories()->whereNull('deleted_at');
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope to get active subsystems.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Get all processes through process categories.
     */
    public function getAllProcesses()
    {
        return $this->processCategories()
            ->with('processes')
            ->get()
            ->flatMap(function ($category) {
                return $category->processes;
            });
    }
}
