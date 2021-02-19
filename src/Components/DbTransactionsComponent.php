<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

class DbTransactionsComponent extends ComponentAbstract
{
    public function register(): void
    {
        $this->listen(TransactionBeginning::class, function (): void {
            $this->statsd->increment($this->getStat('db.transaction'), 1, [
                'status' => 'begin',
            ]);
        });

        $this->listen(TransactionCommitted::class, function (): void {
            $this->statsd->increment($this->getStat('db.transaction'), 1, [
                'status' => 'commit',
            ]);
        });

        $this->listen(TransactionRolledBack::class, function (): void {
            $this->statsd->increment($this->getStat("db.transaction"), 1, [
                'status' => 'rollback',
            ]);
        });
    }
}
