<?php

namespace App\Repositories;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class InboxRepository
{
    /**
     * Intenta registrar el mensaje en inbox_events.
     * Devuelve true si se insertó (primera vez), false si ya existía (idempotente).
     */
    public function tryStore(string $topic, int $partition, int $offset, ?string $key, array $headers, array $payload): bool
    {
        try {
            DB::table('inbox_events')->insert([
                'topic'       => $topic,
                'partition'   => $partition,
                'offset'      => $offset,
                'key'         => $key,
                'headers'     => json_encode($headers, JSON_UNESCAPED_UNICODE),
                'payload'     => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'received_at' => now(),
            ]);
            return true;
        } catch (QueryException $e) {
            // Violación de UNIQUE(topic,partition,offset)
            if (str_contains($e->getMessage(), 'inbox_events_topic_partition_offset_unique')) {
                return false;
            }
            throw $e;
        }
    }

    public function markProcessed(string $topic, int $partition, int $offset): void
    {
        DB::table('inbox_events')
            ->where(compact('topic', 'partition', 'offset'))
            ->update(['processed_at' => now(), 'error' => null]);
    }

    public function markFailed(string $topic, int $partition, int $offset, string $error): void
    {
        DB::table('inbox_events')
            ->where(compact('topic', 'partition', 'offset'))
            ->update(['error' => mb_substr($error, 0, 5000)]);
    }
}
