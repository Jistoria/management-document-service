<?php

namespace App\Models;

use App\Traits\Auditable; // 👈 Agregar trait de auditoría
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for Metadata Schema Events
 *
 * Represents events that occur in the metadata schema system.
 * Used for auditing and tracking changes across microservices.
 *
 * @property string $id
 * @property string $schema_id
 * @property string $event_type
 * @property string|null $actor_id
 * @property \Carbon\Carbon $event_time
 * @property array|null $details
 * @property string|null $correlation_id
 * @property string|null $external_user_id
 * @property string|null $service_version
 */
class MetadataSchemaEvent extends Model
{
    use HasFactory, HasUuids, Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'metadata_schema_events';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'schema_id',
        'event_type',
        'actor_id',
        'event_time',
        'details',
        'correlation_id',
        'external_user_id',
        'service_version'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'event_time' => 'datetime',
        'details' => 'array'
    ];

    /**
     * Common event types.
     */
    const EVENT_TYPES = [
        'schema_created',
        'schema_updated',
        'schema_deleted',
        'field_added',
        'field_updated',
        'field_removed',
        'version_created',
        'materialized_view_refresh',
        'api_integration_updated',
        'cache_invalidated'
    ];

    /**
     * Get the metadata schema that owns this event.
     */
    public function metadataSchema(): BelongsTo
    {
        return $this->belongsTo(MetadataSchema::class, 'schema_id');
    }

    /**
     * Scope to filter by schema.
     */
    public function scopeBySchema($query, string $schemaId)
    {
        return $query->where('schema_id', $schemaId);
    }

    /**
     * Scope to filter by event type.
     */
    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope to filter by external user.
     */
    public function scopeByExternalUser($query, string $externalUserId)
    {
        return $query->where('external_user_id', $externalUserId);
    }

    /**
     * Scope to filter by correlation ID (for distributed tracing).
     */
    public function scopeByCorrelationId($query, string $correlationId)
    {
        return $query->where('correlation_id', $correlationId);
    }

    /**
     * Scope to filter by service version.
     */
    public function scopeByServiceVersion($query, string $serviceVersion)
    {
        return $query->where('service_version', $serviceVersion);
    }

    /**
     * Scope to order by event time (newest first).
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('event_time', 'desc');
    }

    /**
     * Scope to order by event time (oldest first).
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('event_time', 'asc');
    }

    /**
     * Scope to filter events within a date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_time', [$startDate, $endDate]);
    }

    /**
     * Create a new event record.
     */
    public static function createEvent(
        string $schemaId,
        string $eventType,
        ?string $actorId = null,
        ?array $details = null,
        ?string $correlationId = null,
        ?string $externalUserId = null,
        ?string $serviceVersion = null
    ) {
        return static::create([
            'schema_id' => $schemaId,
            'event_type' => $eventType,
            'actor_id' => $actorId,
            'event_time' => now(),
            'details' => $details,
            'correlation_id' => $correlationId,
            'external_user_id' => $externalUserId,
            'service_version' => $serviceVersion
        ]);
    }

    /**
     * Get formatted event details.
     */
    public function getFormattedDetails(): string
    {
        if (empty($this->details)) {
            return '';
        }

        return json_encode($this->details, JSON_PRETTY_PRINT);
    }

    /**
     * Check if this event is part of a distributed transaction.
     */
    public function isDistributedTransaction(): bool
    {
        return !empty($this->correlation_id);
    }

    /**
     * Get related events with the same correlation ID.
     */
    public function getRelatedEvents()
    {
        if (!$this->isDistributedTransaction()) {
            return collect();
        }

        return static::where('correlation_id', $this->correlation_id)
            ->where('id', '!=', $this->id)
            ->latest()
            ->get();
    }
}
