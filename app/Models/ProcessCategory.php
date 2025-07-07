<?php

namespace App\Models;

use App\Traits\Auditable; // 👈 Agregar trait de auditoría
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for Process Categories
 *
 * Represents categories of processes within a subsystem.
 * Process categories group related processes together.
 *
 * @property string $id
 * @property string $subsystem_id
 * @property string $name
 * @property string|null $code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class ProcessCategory extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'process_categories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'subsystem_id',
        'name',
        'code'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Get the subsystem that owns this process category.
     */
    public function subsystem(): BelongsTo
    {
        return $this->belongsTo(Subsystem::class);
    }

    /**
     * Get the processes for this process category.
     */
    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }

    /**
     * Get active processes for this process category.
     */
    public function activeProcesses(): HasMany
    {
        return $this->processes()->whereNull('deleted_at');
    }

    /**
     * Get root processes (processes without parent) for this category.
     */
    public function rootProcesses(): HasMany
    {
        return $this->processes()->whereNull('parent_id')->whereNull('deleted_at');
    }

    /**
     * Scope to filter by subsystem.
     */
    public function scopeBySubsystem($query, string $subsystemId)
    {
        return $query->where('subsystem_id', $subsystemId);
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope to get active process categories.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
