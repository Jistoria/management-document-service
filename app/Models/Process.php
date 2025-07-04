<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for Processes
 *
 * Represents individual processes within a process category.
 * Processes can have hierarchical relationships (parent-child).
 *
 * @property string $id
 * @property string $process_category_id
 * @property string|null $parent_id
 * @property string $name
 * @property string|null $code
 * @property int $order
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Process extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'processes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'process_category_id',
        'parent_id',
        'name',
        'code',
        'order'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'order' => 'integer'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Get the process category that owns this process.
     */
    public function processCategory(): BelongsTo
    {
        return $this->belongsTo(ProcessCategory::class);
    }

    /**
     * Get the parent process.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'parent_id');
    }

    /**
     * Get the child processes.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Process::class, 'parent_id');
    }

    /**
     * Get active child processes.
     */
    public function activeChildren(): HasMany
    {
        return $this->children()->whereNull('deleted_at')->orderBy('order');
    }

    /**
     * Get the required documents for this process.
     */
    public function requiredDocuments(): HasMany
    {
        return $this->hasMany(RequiredDocument::class);
    }

    /**
     * Get active required documents for this process.
     */
    public function activeRequiredDocuments(): HasMany
    {
        return $this->requiredDocuments()
            ->whereNull('deleted_at')
            ->orderBy('order');
    }

    /**
     * Scope to filter by process category.
     */
    public function scopeByProcessCategory($query, string $processCategoryId)
    {
        return $query->where('process_category_id', $processCategoryId);
    }

    /**
     * Scope to filter by parent process.
     */
    public function scopeByParent($query, ?string $parentId = null)
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Scope to get root processes (no parent).
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
     * Scope to get active processes.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope to order by order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get all ancestors of this process.
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
     * Get all descendants of this process.
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
     * Get the full hierarchical path of this process.
     */
    public function getFullPath(): string
    {
        $ancestors = $this->getAncestors();
        $path = $ancestors->pluck('name')->join(' > ');

        return $path ? $path . ' > ' . $this->name : $this->name;
    }
}
