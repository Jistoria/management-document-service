<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use App\Traits\Auditable; // 👈 Agregar trait de auditoría
use App\Traits\HasCamelCaseAttributes; // 👈 Agregar trait para camelCase
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for Departments
 *
 * Represents departments within the head office.
 * Departments contain careers and are part of the organizational hierarchy.
 *
 * @property string $id
 * @property string $head_office_id
 * @property string $name
 * @property string|null $code
 * @property string|null $code_numeric
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property int $version
 * @property mixed $headOffice
 * @method static create(array $data)
 */
class Department extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasAuditFields, Auditable, HasCamelCaseAttributes {
        Auditable::getCurrentExternalUserId insteadof HasAuditFields;
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'departments';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'head_office_id',
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
     * Get the head office that owns this department.
     */
    public function headOffice(): BelongsTo
    {
        return $this->belongsTo(HeadOffice::class);
    }

    /**
     * Get the subsystems associated with this department.
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
     * Get the careers for this department.
     */
    public function careers(): HasMany
    {
        return $this->hasMany(Career::class);
    }

    /**
     * Get active careers for this department.
     */
    public function activeCareers(): HasMany
    {
        return $this->careers()->whereNull('deleted_at');
    }

    /**
     * Scope to filter by head office.
     */
    public function scopeByHeadOffice($query, string $headOfficeId)
    {
        return $query->where('head_office_id', $headOfficeId);
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope to get active departments.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope to get careers with or without subsystems.
     */
    public function scopeHasSubsystems($query, $value = true)
    {
        return $value ? $query->has('subsystems') : $query->doesntHave('subsystems');
    }

    /**
     * Scope to get careers with id subsystem.
     */
    public function scopeWithSubsystemId($query, string $subsystemId)
    {
        return $query->whereHas('subsystems', function ($q) use ($subsystemId) {
            $q->where('subsystems.id', $subsystemId);
        });
    }

    /**
     * Scope to get careers without a specific subsystem.
     */
    public function scopeWithoutSubsystemId($query, string $subsystemId)
    {
        return $query->whereDoesntHave('subsystems', function ($q) use ($subsystemId) {
            $q->where('subsystems.id', $subsystemId);
        });
    }

    /**
     * Get full department hierarchy with all relationships.
     * This method loads the complete hierarchy for an existing model instance.
     */
    public function getFullHierarchy()
    {
        return $this->load([
            'headOffice',
            'careers.subsystems'
        ]);
    }
}
