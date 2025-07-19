<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use App\Traits\Auditable;
use App\Traits\HasCamelCaseAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubsystemGroup extends Model
{
    use HasFactory, HasUuids, HasAuditFields, Auditable, HasCamelCaseAttributes {
        Auditable::getCurrentExternalUserId insteadof HasAuditFields;
    }

    protected $table = 'subsystem_groups';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_public',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_public' => 'boolean'
    ];

    public function subsystems(): BelongsToMany
    {
        return $this->belongsToMany(Subsystem::class, 'subsystem_group_links', 'group_id', 'subsystem_id')
            ->withTimestamps();
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
