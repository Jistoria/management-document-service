<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for Departments
 *
 * Represents departments within a head office.
 * Departments contain careers and are part of the organizational hierarchy.
 *
 * @property string $id
 * @property string $head_office_id
 * @property string $name
 * @property string|null $code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property int $version
 */
class Department extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasAuditFields;

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
}
