<?php

namespace App\Console\Commands;

use App\Kafka\Contracts\MessageHandler;
use App\Kafka\Handlers\PermissionGrantedHandler;
use App\Kafka\Handlers\PermissionRevokedHandler;
use App\Kafka\Handlers\UserDeletedHandler;
use App\Kafka\Handlers\UserRestoredHandler;
use App\Kafka\Handlers\UserUpdatedHandler;
use App\Kafka\Topics;
use App\Repositories\InboxRepository;
use App\Support\KafkaMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\ConsumedMessage;

class AuthSyncConsume extends Command
{
    protected $signature = 'auth-sync:consume
                            {--once : Procesa y sale (para pruebas/CI)}
                            {--timeout=0 : Segundos de timeout del loop (0 = infinito)}';

    protected $description = 'Consume eventos de auth-service y proyecta tablas espejo (usuarios y permisos).';

    /** @var array<string, MessageHandler> */
    private array $handlers;

    public function __construct(
        private InboxRepository $inbox,
        PermissionGrantedHandler $granted,
        PermissionRevokedHandler $revoked,
        UserUpdatedHandler $updated,
        UserDeletedHandler $deleted,
        UserRestoredHandler $restored,
    ) {
        parent::__construct();

        $this->handlers = collect([
            $granted,
            $revoked,
            $updated,
            $deleted,
            $restored,
        ])->keyBy(fn(MessageHandler $h) => $h->topic())->all();
    }

    public function handle(): int
    {
        $topics = array_map('trim', explode(',', env('AUTH_SYNC_TOPICS', implode(',', Topics::all()))));

        $consumer = Kafka::consumer()
            ->subscribe($topics)
            ->withHandler(function (ConsumedMessage $msg) {
                $km = new KafkaMessage($msg);

                $topic = $km->topic();
                $part  = $km->partition();
                $off   = $km->offset();

                // 1) Idempotencia: intentar registrar en inbox
                $inserted = $this->inbox->tryStore(
                    $topic,
                    $part,
                    $off,
                    $km->key(),
                    $km->headers(),
                    $km->payload()
                );
                if (!$inserted) {
                    // Ya procesado: confirmar offset y salir
                    return;
                }

                try {
                    // 2) Proyección dentro de una transacción
                    DB::transaction(function () use ($km, $topic) {
                        $handler = $this->handlers[$topic] ?? null;
                        if (!$handler) {
                            throw new \RuntimeException("No hay handler para topic [$topic]");
                        }
                        $handler->handle($km);
                    });

                    // 3) Marcar OK e informar
                    $this->inbox->markProcessed($topic, $km->partition(), $km->offset());
                } catch (\Throwable $e) {
                    $this->inbox->markFailed($topic, $km->partition(), $km->offset(), $e->getMessage());
                    // Re-lanzar para que NO se confirme el offset y se reintente
                    throw $e;
                }
            })
            ->withAutoCommit(false) // confirmamos offsets solo si no hubo excepción
            ->withCommitBatchSize(1) // confirmación inmediata por mensaje OK
            ->build();

        $timeout = (int)$this->option('timeout');
        if ($this->option('once')) {
            // Procesa un batch y termina (útil para CI/jobs)
            $consumer->consume(1);
            return self::SUCCESS;
        }

        // Loop “infinito” controlado por timeout opcional
        $start = time();
        while (true) {
            $consumer->consume();
            if ($timeout > 0 && (time() - $start) >= $timeout) {
                break;
            }
        }

        return self::SUCCESS;
    }
}
