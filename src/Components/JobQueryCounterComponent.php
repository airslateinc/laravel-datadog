<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Components;

use AirSlate\Datadog\Exceptions\CounterException;
use AirSlate\Datadog\Services\ClassShortener;
use AirSlate\Datadog\Services\CounterManager;
use AirSlate\Datadog\Services\Datadog;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

class JobQueryCounterComponent extends ComponentAbstract
{
    /** @var CounterManager  */
    private $counterManager;

    public function __construct(
        Application $application,
        ClassShortener $classShortener,
        Datadog $datadog,
        Dispatcher $dispatcher,
        CounterManager $counterManager
    ) {
        parent::__construct(
            $application,
            $classShortener,
            $datadog,
            $dispatcher
        );

        $this->counterManager = $counterManager;
    }

    public function register(): void
    {
        $this->listen(JobProcessing::class, function (): void {
            $this->counterManager->startCounter($this->getStat('queue.db.queries'));
        });

        $this->listen(QueryExecuted::class, function (): void {
            $this->counterManager->incrementCounter($this->getStat('queue.db.queries'));
        });

        $this->listen(JobProcessed::class, function (JobProcessed $jobProcessed): void {
            $tags = [
                'status' => 'processed',
                'queue' => $jobProcessed->job->getQueue(),
                'task' => $this->classShortener->shorten($jobProcessed->job->resolveName())
            ];

            $this->saveStat($tags);
        });

        $this->listen(JobExceptionOccurred::class, function (JobExceptionOccurred $event): void {
            $tags = [
                'status' => 'exceptionOccurred',
                'queue' => $event->job->getQueue(),
                'task' => $this->classShortener->shorten($event->job->resolveName()),
                'exception' => $this->classShortener->shorten(get_class($event->exception))
            ];

            $this->saveStat($tags);
        });

        $this->listen(JobFailed::class, function (JobFailed $event): void {
            $tags = [
                'status' => 'failed',
                'queue' => $event->job->getQueue(),
                'task' => $this->getShortName($event->job->resolveName()),
                'exception' => $this->getShortName(get_class($event->exception))
            ];

            $this->saveStat($tags);
        });
    }

    /**
     * @param array<string, string> $tags
     * @throws CounterException
     */
    private function saveStat(array $tags): void
    {
        $name = $this->getStat('queue.db.queries');

        $this->statsd->gauge(
            $name,
            $this->counterManager->getValue($name),
            1,
            $tags
        );
    }
}
