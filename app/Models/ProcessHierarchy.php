<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Model for Process Hierarchy Materialized View
 *
 * Represents the materialized view that contains the complete
 * hierarchical structure of processes for optimized queries.
 *
 * @property string $id
 * @property string|null $parent_id
 * @property string $name
 * @property string|null $code
 * @property string $process_category_id
 * @property int $order
 * @property int $level
 * @property array $path
 * @property string $full_path
 * @property string $category_name
 * @property string|null $category_code
 */
class ProcessHierarchy extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'mv_process_hierarchy';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'order' => 'integer',
        'level' => 'integer',
        'path' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     * Note: Materialized views are read-only, so this is mainly for documentation.
     */
    protected $fillable = [];

    /**
     * Get the process category for this hierarchy item.
     */
    public function processCategory(): BelongsTo
    {
        return $this->belongsTo(ProcessCategory::class, 'process_category_id');
    }

    /**
     * Get the parent process in the hierarchy.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProcessHierarchy::class, 'parent_id');
    }

    /**
     * Scope to filter by process category.
     */
    public function scopeByProcessCategory($query, string $processCategoryId)
    {
        return $query->where('process_category_id', $processCategoryId);
    }

    /**
     * Scope to filter by level in hierarchy.
     */
    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope to get root level processes (level 0).
     */
    public function scopeRoots($query)
    {
        return $query->where('level', 0);
    }

    /**
     * Scope to filter by parent process.
     */
    public function scopeByParent($query, ?string $parentId = null)
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Scope to get children of a specific process.
     */
    public function scopeChildrenOf($query, string $processId)
    {
        return $query->where('parent_id', $processId);
    }

    /**
     * Scope to get descendants of a specific process.
     */
    public function scopeDescendantsOf($query, string $processId)
    {
        // Get the path of the parent process
        $parentPath = static::where('id', $processId)->value('path');

        if (!$parentPath) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        // Find all processes that have the parent process in their path
        return $query->whereRaw('? = ANY(path)', [$processId])
            ->where('id', '!=', $processId);
    }

    /**
     * Scope to order by hierarchy (level, then order).
     */
    public function scopeOrderedHierarchy($query)
    {
        return $query->orderBy('level')
            ->orderBy('order');
    }

    /**
     * Scope to filter by category code.
     */
    public function scopeByCategoryCode($query, string $categoryCode)
    {
        return $query->where('category_code', $categoryCode);
    }

    /**
     * Get all children processes at the next level.
     */
    public function getDirectChildren()
    {
        return static::where('parent_id', $this->id)
            ->orderBy('order')
            ->get();
    }

    /**
     * Get all descendant processes at any level below.
     */
    public function getAllDescendants()
    {
        return static::descendantsOf($this->id)
            ->orderedHierarchy()
            ->get();
    }

    /**
     * Get the complete hierarchy tree starting from this process.
     */
    public function getSubTree()
    {
        $descendants = $this->getAllDescendants();

        return $descendants->groupBy('level');
    }

    /**
     * Check if this process has children.
     */
    public function hasChildren(): bool
    {
        return static::where('parent_id', $this->id)->exists();
    }

    /**
     * Check if this process is a leaf (has no children).
     */
    public function isLeaf(): bool
    {
        return !$this->hasChildren();
    }

    /**
     * Check if this process is a root (has no parent).
     */
    public function isRoot(): bool
    {
        return $this->level === 0;
    }

    /**
     * Get the depth of this process in the hierarchy.
     */
    public function getDepth(): int
    {
        return $this->level;
    }

    /**
     * Get the breadcrumb trail for this process.
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $pathIds = $this->path ?? [];

        if (!empty($pathIds)) {
            $processes = static::whereIn('id', $pathIds)
                ->orderByRaw('array_position(?, id::text)', ['{' . implode(',', $pathIds) . '}'])
                ->get();

            foreach ($processes as $process) {
                $breadcrumbs[] = [
                    'id' => $process->id,
                    'name' => $process->name,
                    'code' => $process->code,
                    'level' => $process->level
                ];
            }
        }

        return $breadcrumbs;
    }

    /**
     * Refresh the materialized view.
     */
    public static function refreshMaterializedView(bool $concurrently = true): bool
    {
        try {
            if ($concurrently) {
                DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY mv_process_hierarchy');
            } else {
                DB::statement('REFRESH MATERIALIZED VIEW mv_process_hierarchy');
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get statistics about the hierarchy.
     */
    public static function getHierarchyStats(): array
    {
        return [
            'total_processes' => static::count(),
            'max_depth' => static::max('level'),
            'root_processes' => static::where('level', 0)->count(),
            'leaf_processes' => static::whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('mv_process_hierarchy as children')
                    ->whereColumn('children.parent_id', 'mv_process_hierarchy.id');
            })->count(),
            'categories' => static::distinct('process_category_id')->count()
        ];
    }
}
