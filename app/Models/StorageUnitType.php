<?php

namespace App\Models;

use App\Traits\Auditable; // 👈 Agregar trait de auditoría
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for Storage Unit Types
 *
 * Represents types of storage units in the document storage system.
 * Examples: Building, Floor, Room, Cabinet, Shelf, Box
 *
 * @property string $id
 * @property string $name
 * @property string $code
 * @property boolean $can_have_children
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class StorageUnitType extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'storage_unit_types';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'can_have_children',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'can_have_children' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Get the storage units of this type.
     */
    public function storageUnits(): HasMany
    {
        return $this->hasMany(StorageUnit::class);
    }

    /**
     * Get active storage units of this type.
     */
    public function activeStorageUnits(): HasMany
    {
        return $this->storageUnits()->whereNull('deleted_at');
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope to filter by level.
     */
    public function scopeByCanHaveChildren($query, bool $canHaveChildren = true)
    {
        return $query->where('can_have_children', $canHaveChildren);
    }

    /**
     * Scope to get active storage unit types.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope to order by level.
     */
    public function scopeOrderedByLevel($query)
    {
        return $query->orderBy('level');
    }
}
