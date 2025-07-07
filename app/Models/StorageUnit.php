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
 * Model for Storage Units
 *
 * Represents individual storage units in the document storage system.
 * Storage units can have hierarchical relationships (parent-child).
 *
 * @property string $id
 * @property string $storage_unit_type_id
 * @property string|null $parent_id
 * @property string $label
 * @property string|null $code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class StorageUnit extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'storage_units';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'storage_unit_type_id',
        'parent_id',
        'label',
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
     * Get the storage unit type for this storage unit.
     */
    public function storageUnitType(): BelongsTo
    {
        return $this->belongsTo(StorageUnitType::class);
    }

    /**
     * Get the parent storage unit.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(StorageUnit::class, 'parent_id');
    }

    /**
     * Get the child storage units.
     */
    public function children(): HasMany
    {
        return $this->hasMany(StorageUnit::class, 'parent_id');
    }

    /**
     * Get active child storage units.
     */
    public function activeChildren(): HasMany
    {
        return $this->children()->whereNull('deleted_at');
    }

    /**
     * Scope to filter by storage unit type.
     */
    public function scopeByStorageUnitType($query, string $storageUnitTypeId)
    {
        return $query->where('storage_unit_type_id', $storageUnitTypeId);
    }

    /**
     * Scope to filter by parent storage unit.
     */
    public function scopeByParent($query, ?string $parentId = null)
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Scope to get root storage units (no parent).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to filter by code.
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope to get active storage units.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Get all ancestors of this storage unit.
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Get all descendants of this storage unit.
     */
    public function getDescendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }

    /**
     * Get the full hierarchical path of this storage unit.
     */
    public function getFullPath(): string
    {
        $ancestors = $this->getAncestors();
        $path = $ancestors->pluck('label')->join(' > ');

        return $path ? $path . ' > ' . $this->label : $this->label;
    }

    /**
     * Get the full location code based on hierarchy.
     */
    public function getLocationCode(): string
    {
        $ancestors = $this->getAncestors();
        $codes = $ancestors->pluck('code')->filter()->join('-');

        return $codes ? $codes . '-' . $this->code : $this->code;
    }
}
