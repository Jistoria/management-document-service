<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCamelCaseAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetadataSchemaField extends Pivot
{
    use HasFactory, HasUuids, Auditable, HasCamelCaseAttributes;

    protected $table = 'metadata_schema_fields';

    protected $fillable = [
        'metadata_schema_id',
        'metadata_field_id',
        'is_required',
        'is_repeatable',
        'min_occurs',
        'max_occurs',
        'allow_duplicates',
        'sort_order',
        'default_value',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_repeatable' => 'boolean',
        'allow_duplicates' => 'boolean',
        'sort_order' => 'integer',
        'min_occurs' => 'integer',
        'max_occurs' => 'integer',
    ];

    public function metadataSchema(): BelongsTo
    {
        return $this->belongsTo(MetadataSchema::class, 'metadata_schema_id');
    }

    public function metadataField(): BelongsTo
    {
        return $this->belongsTo(MetadataField::class, 'metadata_field_id');
    }
}
