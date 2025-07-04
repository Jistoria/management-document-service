<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for Academic Roles
 *
 * Represents academic roles within the organization.
 * Examples: Student, Teacher, Coordinator, Dean, Rector
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
class AcademicRole extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasAuditFields;

    /**
     * The table associated with the model.
     */
    protected $table = 'academic_roles';

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
     * Get the required documents that are specific to this academic role.
     */
    public function requiredDocuments(): HasMany
    {
        return $this->hasMany(RequiredDocument::class);
    }

    /**
     * Get active required documents for this academic role.
     */
    public function activeRequiredDocuments(): HasMany
    {
        return $this->requiredDocuments()->whereNull('deleted_at');
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope to get active academic roles.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Get processes that have documents specific to this academic role.
     */
    public function getProcesses()
    {
        return $this->requiredDocuments()
            ->with('process')
            ->get()
            ->pluck('process')
            ->unique('id');
    }
}
