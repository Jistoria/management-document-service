<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use App\Traits\Auditable; // 👈 Agregar trait de auditoría
use App\Traits\HasCamelCaseAttributes; // 👈 Agregar trait para camelCase
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for Careers
 *
 * Represents academic careers within a department.
 * Careers can be associated with multiple subsystems.
 *
 * @property string $id
 * @property string $department_id
 * @property string $name
 * @property string|null $code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property int $version
 */
class Career extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasAuditFields, Auditable, HasCamelCaseAttributes {
        Auditable::getCurrentExternalUserId insteadof HasAuditFields;
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'careers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'department_id',
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
     * Get the department that owns this career.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the subsystems associated with this career via entity links.
     */
    public function subsystemsViaEntityLinks(): MorphToMany
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
     * Get active subsystems associated with this career.
     */
    public function activeSubsystems(): BelongsToMany
    {
        return $this->subsystems()->whereNull('subsystems.deleted_at');
    }

    /**
     * Scope to filter by department.
     */
    public function scopeByDepartment($query, string $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope to get active careers.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Get the head office through department relationship.
     */
    public function getHeadOfficeAttribute()
    {
        return $this->department->headOffice ?? null;
    }
}
